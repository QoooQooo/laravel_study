<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart;   //Cart 라이브러리 호출

class CartController extends Controller
{
    public function addToCart(Request $request) {
        //Cart 클래스 사용법 참고
        //Cart::add('293ad', 'Product 1', 1, 9.99);

        $product = Product::with('product_images')->find($request->id);

        if ($product == null) {
            return response()->json([
                'status' => false,
                'message' => '상품을 찾을 수 없음'
            ]);
        }

        if (Cart::count() > 0) {
            /* echo "장바구니에 같은 물건이 있습니다.";
            $status = false;
            $message = "장바구니에 같은 물건이 있습니다."; */

            $cartContent = Cart::content();
            $productAlreadyExist = false;

            //print_r($cartContent);

            foreach ($cartContent as $item) {
                if ($item->id == $product->id) {
                    $productAlreadyExist = true;
                }
            }

            if ($productAlreadyExist == false) {
                Cart::add(
                    $product->id,
                    $product->title,
                    1,
                    $product->price,
                    ['productImage' => (!empty($product->product_images->first()) ? $product->product_images->first() : '')]
                );

                $status = true;
                $message = $product->title.'을(를) 장바구니에 등록하였습니다.';
                session()->flash('success', $message);

            } else {
                $status = false;
                $message = $product->title.'는(은) 장바구니에 있습니다';
            }

        } else {
            //장바구니 비었을때
            //echo "장바구니가 비었습니다.";
            Cart::add(
                $product->id,
                $product->title,
                1,
                $product->price,
                ['productImage' => (!empty($product->product_images->first()) ? $product->product_images->first() : '')]
            );
            $status = true;
            $message = $product->title.'을(를) 장바구니에 등록하였습니다.';
            session()->flash('success', $message);
        }

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);

    }

    public function cart() {
        $cartContent = Cart::content();

        $data['cartContent'] = $cartContent;

        return view('front.cart', $data);
    }

    public function updateCart(Request $request) {
        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo = Cart::get($rowId);

        $product = Product::find($itemInfo->id);

        if ($product->track_qty == 'Yes') {
            if ($qty <= $product->qty) {
                Cart::update($rowId, $qty);
                $message = "장바구니 수정성공";
                $status = true;
                session()->flash('success', $message);
            } else {
                $message = "상품의 수량(".$qty.")이 부족합니다";
                $status = false;
                session()->flash('error', $message);
            }
        } else {
            Cart::update($rowId, $qty);
            $message = "장바구니 수정성공";
            $status = true;
            session()->flash('success', $message);
        }

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function deleteItem(Request $request) {

        $itemInfo = Cart::get($request->rowId);
        if ($itemInfo == null) {
            $message = "해당 상품을 찾을 수 없슶니다";
            $successError = "error";
            $status = false;
        } else {
            $message = $itemInfo->name."을(를) 장바구니에서 비웠습니다.";
            $successError = "success";
            $status = true;
            Cart::remove($request->rowId);
        }

        session()->flash($successError, $message);
        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function checkout()
    {
        if (Cart::count() == 0) {
            return redirect()->route('front.cart');
        }

        if (Auth::check() == false) {

            if ( !session()->has('url.intended')) {
                session([
                    'url.intended' => url()->current()
                ]);

            }

            return redirect()->route('account.login');
        }

        session()->forget('url.intended');

        $countries = Country::orderby('name', 'ASC')->get();

        return view('front.checkout', [
            'countries' => $countries
        ]);
    }
}
