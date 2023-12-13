<?php

namespace App\Http\Controllers\admin;

use Image;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class ProductImageController extends Controller
{
    public function update(Request $request) {

        $image = $request->image;
        $ext = $image->getClientOriginalExtension();
        //$newName = rand(100000,999999).time().'.'.$ext;
        $sourcePath = $image->getPathName();       //getPathName() php기본함수

        $productImage = new ProductImage();
        $productImage->product_id = $request->product_id;
        $productImage->image = 'NULL';
        $productImage->save();

        $imageName = $request->product_id.'-'.$productImage->id.'-'.rand(100000,999999).time().'.'.$ext;
        $productImage->image = $imageName;
        $productImage->save();

        // 큰이미지
        //$sourcePath = public_path().'/temp/'.$tempImageInfo->name;
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

        return response()->json([
            'status' => true,
            'image_id' => $productImage->id,
            'ImagePath' => asset('uploads/product/small/'.$productImage->image),
            'message' => '이미지 저장 성공'
        ]);

    }

    public function destroy(Request $request) {
        $productImage = ProductImage::find($request->id);

        if (empty($productImage)) {
            return response()->json([
                'status' => false,
                'message' => '이미지를 찾을 수 없습니다.'
            ]);
        }
        //이미지 삭제
        //$imagePath = public_path().''
        File::delete(public_path('uploads/product/large/'.$productImage->image));
        File::delete(public_path('uploads/product/small/'.$productImage->image));

        $productImage->delete();

        return response()->json([
            'status' => true,
            'message' => '이미지 삭제 성공'
        ]);
    }
}

