<?php

namespace App\Http\Controllers;

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
        return view('front.home');

    }
}
