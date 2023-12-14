<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{
    public function index(Request $request) {
        //latest('abc') 설명 : order by abc desc
        //$subCategories = SubCategory::latest('id');
        $subCategories = SubCategory::select('sub_categories.*','categories.name as categoryName')
                            ->latest('sub_categories.id')
                            ->leftJoin('categories','categories.id','sub_categories.category_id');

        if (!empty($request->get('keyword'))) {
            $subCategories = $subCategories->where('sub_categories.name', 'like', '%'.$request->get('keyword').'%');
            $subCategories = $subCategories->orwhere('categories.name', 'like', '%'.$request->get('keyword').'%');
        }

        $subCategories = $subCategories->paginate(10);

        return view('admin.sub_category.list', compact('subCategories'));
    }

    public function create() {
        $categories = Category::orderBy('name', 'ASC')->get();
        $data['categories'] = $categories;
        return view('admin.sub_category.create',$data);
        //return view('admin.sub_category.create',compact('categories'));
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:sub_categories',
            'category' => 'required',
            'status' => 'required'
        ]);

        if ($validator->passes()) {

            $subCategory = new SubCategory();
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $request->session()->flash('success','하위 카테고리 등록 성공');

            return response([
                'status' => true,
                'message' => '하위 카테고리 등록 성공'
            ]);

        } else {
            return response([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit($subCategoryId, Request $request) {

        $subCategory = SubCategory::find($subCategoryId);
        $categories = Category::orderBy('name', 'ASC')->get();
        $data['categories'] = $categories;
        $data['subCategory'] = $subCategory;

        if (empty($subCategory)) {
            $request->session()->flash('error','해당 하위 카테고리를 찾을 수 없습니다.');
            return redirect()->route('sub-category.index');
        }

        return view('admin.sub_category.edit', $data);
    }

    public function update($subCategoryId, Request $request) {

        $subCategory = SubCategory::find($subCategoryId);

        if (empty($subCategory)) {
            $request->session()->flash('error','해당 하위 카테고리를 찾을 수 없습니다.');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => '해당 하위 카테고리를 찾을 수 없습니다.'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:sub_categories,slug,'.$subCategory->id.',id',
            'category' => 'required',
            'status' => 'required'
        ]);

        if ($validator->passes()) {

            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $request->session()->flash('success','하위 카테고리 수정 성공');

            return response([
                'status' => true,
                'message' => '하위 카테고리 수정 성공'
            ]);

        } else {
            return response([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

    }

    public function destroy($subCategoryId, Request $request) {
        $subCategory = SubCategory::find($subCategoryId);

        if (empty($subCategory)) {
            //return redirect()->route('category.index');
            $request->session()->flash('error', '해당 하위 카테고리를 찾을 수 없음');
            return response()->json([
                'status' => true,
                'message' => '해당 하위 카테고리를 찾을 수 없음',
            ]);
        }

        $subCategory->delete();

        $request->session()->flash('success', '하위 카테고리 삭제 성공');

        return response()->json([
            'status' => true,
            'message' => '하위 카테고리 삭제 성공',
        ]);
    }
}
