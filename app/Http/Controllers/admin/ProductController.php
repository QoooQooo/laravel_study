<?php

namespace App\Http\Controllers\admin;

use Image;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\TempImage;
use App\Models\SubCategory;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request) {

        //$products = Product::latest('id')->with('product_images')->paginate(10);
        $products = Product::latest('id')->with('product_images');

        //if ($request->get('keyword') != "") {
        if (!empty($request->get('keyword'))) {
            $products = $products->where('title', 'like', '%'.$request->get('keyword').'%');
            //$products = $products->where('title', 'like', '%'.$request->keyword.'%');     //결과같음
        }

        $products = $products->paginate(10);

        $data['products'] = $products;

        return view('admin.products.list', compact('products'));
    }

    public function create() {
        $data = [];
        $categories = Category::orderBy('name', 'ASC')->get();
        $brands = Brand::orderBy('name', 'ASC')->get();
        $data['categories'] = $categories;
        $data['brands'] = $brands;
        return view('admin.products.create', $data);
    }

    public function store(Request $request) {

        //dd($request->image_array);
        //exit;

        $rules = [
            'title' => 'required',
            'slug' => 'required|unique:products',
            'price' => 'required|numeric',
            'sku' => 'required|unique:products',
            'track_qty' => 'required|in:Yes,No',
            'category' => 'required|numeric',
            'is_featured' => 'required|in:Yes,No'
        ];

        if (!empty($request->track_qty) && $request->track_qty == 'Yes') {
            $rules['qty'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(),$rules);

        if ($validator->passes()) {

            $product = new Product;
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->short_description = $request->short_description;
            $product->shopping_returns = $request->shopping_returns;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->track_qty = $request->track_qty;
            $product->qty = $request->qty;
            $product->status = $request->status;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brands;
            $product->is_featured = $request->is_featured;
            //$product->related_products = $request->related_products;
            $product->related_products = (!empty($request->related_products)) ? implode(',', $request->related_products) : '';
            $product->save();

            //갤러리 이미지 저장하기
            if (!empty($request->image_array)) {
                foreach ($request->image_array as $temp_image_id) {

                    $tempImageInfo = TempImage::find($temp_image_id);
                    $extArray = explode('.', $tempImageInfo->name);
                    $ext = last($extArray);

                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->image = 'NULL';
                    $productImage->save();

                    $imageName = $product->id.'-'.$productImage->id.'-'.rand(100000,999999).time().'.'.$ext;
                    $productImage->image = $imageName;
                    $productImage->save();

                    // 상품썸네일 만들기

                    // 큰이미지
                    $sourcePath = public_path().'/temp/'.$tempImageInfo->name;
                    $destLPath = public_path().'/uploads/product/large/'.$imageName;
                    $image = Image::make($sourcePath);
                    $image->resize(1400, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $image->save($destLPath);

                    // 작은이미지
                    $destSPath = public_path().'/uploads/product/small/'.$imageName;
                    $image = Image::make($sourcePath);
                    $image->fit(300,300);
                    $image->save($destSPath);

                }
            }

            $request->session()->flash('success', '상품등록 성공');

            return response()->json([
                'status' => true,
                'message' => '상품둥록 성공'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit($id, Request $request) {

        $product = Product::find($id);
        if (empty($product)) {
            //$request->session->flash('error', '상품을 찾을 수 없습니다.');        //같은결과
            return redirect()->route('products.index')->with('error', '상품을 찾을 수 없습니다.');
        }

        $categories = Category::orderBy('name', 'ASC')->get();
        $subCategories = SubCategory::where('category_id', $product->category_id)->get();
        $brands = Brand::orderBy('name', 'ASC')->get();
        $productImages = ProductImage::where('product_id',$product->id)->get();

        //관계상품목록생성
        $relatedProducts = [];
        if ($product->related_products != '') {
            $productArray = explode(',', $product->related_products);
            $relatedProducts = Product::whereIn('id', $productArray)->with('product_images')->get();
        }

        $data = [];
        $data['product'] = $product;
        $data['categories'] = $categories;
        $data['subCategories'] = $subCategories;
        $data['brands'] = $brands;
        $data['productImages'] = $productImages;
        $data['relatedProducts'] = $relatedProducts;


        return view('admin.products.edit', $data);
    }

    public function update($id, Request $request) {

        $product = Product::find($id);
        /* if (empty($product)) {
            //$request->session()->flash('error','해당 상품을 찾을 수 없습니다.');      //같은결과
            return redirect()->route('products.index')->with('error','해당 상품을 찾을 수 없습니다.');
        } */

        $rules = [
            'title' => 'required',
            'slug' => 'required|unique:products,slug,'.$product->id.',id',
            'price' => 'required|numeric',
            'sku' => 'required|unique:products,sku,'.$product->id.',id',
            'track_qty' => 'required|in:Yes,No',
            'category' => 'required|numeric',
            'is_featured' => 'required|in:Yes,No'
        ];

        if (!empty($request->track_qty) && $request->track_qty == 'Yes') {
            $rules['qty'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(),$rules);

        if ($validator->passes()) {

            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->short_description = $request->short_description;
            $product->shopping_returns = $request->shopping_returns;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->track_qty = $request->track_qty;
            $product->qty = $request->qty;
            $product->status = $request->status;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brands;
            $product->is_featured = $request->is_featured;
            //$product->related_products = $request->related_products;
            $product->related_products = (!empty($request->related_products)) ? implode(',', $request->related_products) : '';
            $product->save();


            $request->session()->flash('success', '상품수정 성공');

            return response()->json([
                'status' => true,
                'message' => '상품수정 성공'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($id, Request $request) {
        $product = Product::find($id);

        if (empty($product)) {
            $request->session()->flash('error', '상품을 찾을 수 없습니다.');
            return response()->json([
                'status' => false,
                'notFound' => true,
            ]);
        }
        $productImages = ProductImage::where('product_id', $id)->get();

        if (!empty($productImages)) {
            foreach ($productImages as $productImage) {
                File::delete(public_path('uploads/product/large/'.$productImage->image));
                File::delete(public_path('uploads/product/small/'.$productImage->image));
            }

            ProductImage::where('product_id', $id)->delete();
        }

        $product->delete();

        $request->session()->flash('success', '상품삭제성공');

        return response()->json([
            'status' => true,
            'message' => '상품삭제성공'
        ]);

    }

    public function getProducts(Request $request){

        $tempProduct = [];
        if ($request->term != "") {
            $products = Product::where('title','like','%'.$request->term.'%')->get();

            if ($products != null) {
                foreach ($products as $product) {
                    $tempProduct[] = array(
                        'id' => $product->id,
                        'text' => $product->title
                    );
                }
            }
        }

        return response()->json([
            'tags' => $tempProduct,
            'status' => true
        ]);
    }
}

