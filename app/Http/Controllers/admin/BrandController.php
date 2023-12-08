<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    public function index(Request $request) {
        $brands = Brand::latest('id');

        if (!empty($request->get('keyword'))) {
            $brands = $brands->where('name', 'like', '%'.$request->get('keyword').'%');
        }

        $brands = $brands->paginate(10);
        return view('admin.brand.list',compact('brands'));

    }

    public function create() {
        return view('admin.brand.create');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:brands',
        ]);

        if ($validator->passes()) {
            $brand = new Brand();
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            return response()->json([
                'status' => true,
                'message' => '브랜드 등록 성공'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit($brandId, Request $request) {
        $brand = Brand::find($brandId);
        if (empty($brand)) {
            $request->session()->flash('error','해당 브랜드를 찾을 수 없습니다.');
            return redirect()->route('brands.index');
        }

        return view('admin.brand.edit', compact('brand'));
    }

    public function update($brandId, Request $request) {

        $brand = Brand::find($brandId);

        if (empty($brand)) {
            $request->session()->flash('error','해당 브랜드를 찾을 수 없습니다.');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => '해당 브랜드를 찾을 수 없습니다.'
            ]);
        }

        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,'.$brand->id.',id',
        ]);

        if ($validator->passes()) {

            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            $request->session()->flash('success','브랜드 수정 성공');

            return response()->json([
                'status' => true,
                'message' => '브랜드 수정 성공'
            ]);

        } else {
            return response()->json([
                'status' => FALSE,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($brandId, Request $request) {
        $brand = Brand::find($brandId);

        if (empty($brand)) {
            $request->session()->flash('error', '해당 브랜드를 찾을 수 없음');
            return response()->json([
                'status' => true,
                'message' => '해당 브랜드를 찾을 수 없음',
            ]);
        }

        $brand->delete();

        $request->session()->flash('success', '브랜드 삭제 성공');

        return response()->json([
            'status' => true,
            'message' => '브랜드 삭제 성공',
        ]);
    }
}
