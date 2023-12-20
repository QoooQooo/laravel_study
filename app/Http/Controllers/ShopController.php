<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request, $categorySlug = null, $subCategorySlug = null) {
        $categorySelected = "";
        $subCategorySelected = "";
        $brandsArray = [];

        $categories = Category::orderBy('name', 'ASC')->with('sub_category')->where('status', '1')->get();
        $brands = Brand::orderBy('name', 'ASC')->where('status', '1')->get();
        //$products = Product::orderBy('id', 'DESC')->where('status', '1')->get();

        //프로덕트 서브메뉴클릭 호출 조립
        $products = Product::where('status', '1');
        if (!empty($categorySlug)) {
            $category = Category::where('slug', $categorySlug)->first();
            $products = $products->where('category_id', $category->id);
            $categorySelected = $category->id;
        }

        if (!empty($subCategorySlug)) {
            $subCategory = SubCategory::where('slug', $subCategorySlug)->first();
            $products = $products->where('sub_category_id', $subCategory->id);
            $subCategorySelected = $subCategory->id;
        }

        if (!empty($request->get('brand'))) {
            $brandsArray = explode(',', $request->get('brand'));
            $products = $products->whereIn('brand_id', $brandsArray);
        }

        if ($request->get('price_max') != '' && $request->get('price_min') != '') {
            if ($request->get('price_max') == 1000) {
                $products = $products->whereBetween('price', [intval($request->get('price_min')), 1000000]);
            } else {
                $products = $products->whereBetween('price', [intval($request->get('price_min')), intval($request->get('price_max'))]);
            }
        }



        switch($request->get('sort')){
            case 'price_asc':
                $products = $products->orderBy('price', 'ASC');
                break;
            case 'price_desc':
                $products = $products->orderBy('price', 'DESC');
                break;
            default:
                $products = $products->orderBy('id', 'DESC');
                break;
        }


        //$products = $products->get();
        $products = $products->paginate(3);

        $data['categories'] = $categories;
        $data['brands'] = $brands;
        $data['products'] = $products;
        $data['categorySelected'] = $categorySelected;
        $data['subCategorySelected'] = $subCategorySelected;
        $data['brandsArray'] = $brandsArray;
        $data['priceMin'] = intval($request->get('price_min'));
        //$data['priceMax'] = intval($request->get('price_max'));
        $data['priceMax'] = (intval($request->get('price_max')) == 0 ) ? 1000 : $request->get('price_max');
        $data['sort'] = $request->get('sort');

        return view('front.shop', $data);
    }

    public function product($slug){

        $product = Product::where('slug', $slug)->with('product_images')->first();
        if ($product == null) {
            abort(404);
        }

        $data['product'] = $product;

        return view('front.product', $data);
    }
}
