<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;        //파일을 다루기 위하여 호출
use Image;                                  //이미지 썸네일 생성을 위하여 호출

class CategoryController extends Controller
{
    public function index(Request $request) {
        $categories = Category::latest();

        if (!empty($request->get('keyword'))) {
            $categories = $categories->where('name', 'like', '%'.$request->get('keyword').'%');
        }

        $categories = $categories->paginate(10);

        //$categories = Category::latest()->paginate(10);
        //dd($categories);
        //$data['categories'] = $categories;
        return view('admin.category.list', compact('categories'));
    }

    public function create() {
        return view('admin.category.create');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:categories',
        ]);

        if ($validator->passes()) {

            $category = new Category();
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->save();

            //이미지 저장
            if (!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id.'.'.$ext;
                $sPath = public_path().'/temp/'.$tempImage->name;
                $dPath = public_path().'/uploads/category/'.$newImageName;

                File::copy($sPath,$dPath);  //임시파일 보관 폴더로 복사

                //썸네일 생성
                $tPath = public_path().'/uploads/category/thumb/'.$newImageName;
                $img = Image::make($sPath);
                //$img->resize(200, 200);

                // resize the image to a height of 200 and constrain aspect ratio (auto width)
                $img->resize(null, 200, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $img->save($tPath);

                File::delete($sPath);       //임시파일 삭제

                $category->image = $newImageName;
                $category->save();
            }

            $request->session()->flash('success','카테고리 등록 성공');

            return response()->json([
                'status' => true,
                'message' => '카테고리 등록 성공'
            ]);

        } else {
            return response()->json([
                'status' => FALSE,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit($categoryId, Request $request) {
        $category = Category::find($categoryId);
        if (empty($category)) {
            return redirect()->route('category.index');
        }

        return view('admin.category.edit', compact('category'));
    }

    public function update() {

    }

    public function destroy() {

    }
}
