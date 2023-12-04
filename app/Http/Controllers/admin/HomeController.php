<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(){
        return view('admin.dashboard');
        //$admin = Auth::guard('admin')->user();
        //echo "어서와 라라벨은 처음이지?".$admin->name." <a href='".route('admin.logout')."'>나갈래?</a> ";
    }

    public function logout(){
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }

}
