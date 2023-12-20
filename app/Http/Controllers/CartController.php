<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
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

        } else {
            //장바구니 비었을때

            Cart::add(
                $product->id,
                $product->title,
                1,
                $product->price,
                ['productImage' => (!empty($product->product_images->first()) ? $product->product_images->first() : '')]
            );
        }

    }

    public function cart() {


        //return view('front.cart');
    }
}
