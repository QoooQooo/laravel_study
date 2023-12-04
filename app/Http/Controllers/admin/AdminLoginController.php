<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminLoginController extends Controller
{
    public function index(){
        return view('admin.login'); //view 폴더의 login 블레이드 호출
    }

    //입력값 검증
    public function authenticate(Request $request){

        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->passes()) {

            if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {

                $admin = Auth::guard('admin')->user();

                if ($admin->role == 1) {

                    return redirect()->route('admin.dashboard');

                } else {

                    Auth::guard('admin')->logout();
                    return redirect()->route('admin.login')->with('error', '관리자 권한이 없습니다');
                }

            } else {

                return redirect()->route('admin.login')->with('error', '이메일/비밀번호를 확인해주세요');

            }

        } else {
            return redirect()->route('admin.login')
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

    }
}
