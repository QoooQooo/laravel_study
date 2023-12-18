<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index() {


        /* $subCategories = SubCategory::select('sub_categories.*','categories.name as categoryName')
                            ->latest('sub_categories.id')
                            ->leftJoin('categories','categories.id','sub_categories.category_id'); */

        /* $categories = Category::orderBy('name', 'ASC')
            ->where('showHome','Yes')
            ->get(); */

        //return view('front.home',compact('categories'));

        $products = Product::orderBy('id', 'DESC')->where('is_featured', 'Yes')->where('status', '1')->take(8)->get();
        $data['featuredProducts'] = $products;

        $latestProducts = Product::orderBy('id', 'DESC')->where('status', '1')->take(8)->get();
        $data['latestProducts'] = $latestProducts;

        return view('front.home', $data);

    }
}
