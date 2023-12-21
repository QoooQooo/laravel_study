<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\admin\HomeController;          // 관리자 홈 컨트롤러
use App\Http\Controllers\admin\AdminLoginController;    // 관리자 로그인 컨트롤러
use App\Http\Controllers\admin\BrandController;         // 관리자 브랜드 컨트롤러
use App\Http\Controllers\admin\CategoryController;      // 관리자 카테고리 컨트롤러
use App\Http\Controllers\admin\ProductController;       // 관리자 프로덕트 컨트롤러
use App\Http\Controllers\admin\ProductImageController;  // 관리자 이미지 수정 컨트롤러
use App\Http\Controllers\admin\TempImagesController;    // 관리자 임시 이미지 컨트롤러
use App\Http\Controllers\admin\SubCategoryController;   // 관리자 하위 카테고리 컨트롤러
use App\Http\Controllers\admin\ProductSubCategoryController;       // 관리자 프로덕트 서브카테고리 불러오기컨트롤러

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


//Route::get('/', function () {
//   return view('welcome');
//});


//프론트 첫 페이지 연결
Route::get('/', [FrontController::class, 'index'])->name('front.home');

//상품 진열 페이지 연결
//Route::get('/shop/{a?}/{b?}', [ShopController::class, 'index'])->name('front.shop');
Route::get('/shop/{categorySlug?}/{subCategorySlug?}', [ShopController::class, 'index'])->name('front.shop');


//상품 상세 페이지 연결
Route::get('/product/{slug}', [ShopController::class, 'product'])->name('front.product');

//장바구니
Route::get('/cart', [CartController::class, 'cart'])->name('front.cart');
Route::post('/add-to-cart', [CartController::class, 'addToCart'])->name('front.addToCart');
Route::post('/update-cart', [CartController::class, 'updateCart'])->name('front.updateCart');


//관리자 페이지 연결설정
Route::group(['prefix' => 'admin'], function(){

    Route::group(['middleware' => 'admin.guest'], function(){

        Route::get('/login', [AdminLoginController::class, 'index'])->name('admin.login');
        Route::post('/authenticate', [AdminLoginController::class, 'authenticate'])->name('admin.authenticate');

    });

    Route::group(['middleware' => 'admin.auth'], function(){

        Route::get('/dashboard', [HomeController::class, 'index'])->name('admin.dashboard');
        Route::get('/logout', [HomeController::class, 'logout'])->name('admin.logout');

        //카테고리 페이지 연결설정
        Route::get('/categories', [CategoryController::class, 'index'])->name('category.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('category.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('category.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('category.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('category.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('category.delete');

        //하위 카테고리 페이지 연결설정
        Route::get('/sub-categories', [SubCategoryController::class, 'index'])->name('sub-category.index');
        Route::get('/sub-categories/create', [SubCategoryController::class, 'create'])->name('sub-category.create');
        Route::post('/sub-categories', [SubCategoryController::class, 'store'])->name('sub-category.store');
        Route::get('/sub-categories/{category}/edit', [SubCategoryController::class, 'edit'])->name('sub-category.edit');
        Route::put('/sub-categories/{category}', [SubCategoryController::class, 'update'])->name('sub-category.update');
        Route::delete('/sub-categories/{category}', [SubCategoryController::class, 'destroy'])->name('sub-category.delete');

        //브랜드 페이지 연결설정
        Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
        Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
        Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
        Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.delete');

        //프로덕트 페이지 연결설정
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{products}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.delete');

        //상품등록시 관계상품목록찾기
        Route::get('/get-products', [ProductController::class, 'getProducts'])->name('products.getProducts');

        //프로덕트 페이지에서 카테고리 선택에 따른 하위 카테고리 불러오기 연결설정
        Route::get('/product-subcategories', [ProductSubCategoryController::class, 'index'])->name('product-subcategories.index');

        //상품수정시 이미지 수정
        Route::post('/product-images/update', [ProductImageController::class, 'update'])->name('product-images.update');
        Route::delete('/product-images', [ProductImageController::class, 'destroy'])->name('product-images.destroy');

        //temp-images.create 이미지 임시저장 연결설정
        Route::post('/upload-temp-image', [TempImagesController::class, 'create'])->name('temp-images.create');

        //slug 단어요약
        Route::get('/getSlug', function(Request $request){
            $slug = '';
            if(!empty($request->title)){
                $slug = Str::slug($request->title);
            }
            return response()->json([
                'status' => true,
                'slug' => $slug,
            ]);
        })->name('getSlug');

    });

});
