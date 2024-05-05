<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$path = 'App\Http\Controllers';
Route::get('/login', $path . '\AuthController@login')->name('login');
Route::post('do-login', $path . '\AuthController@doLogin')->name('do-login');
Route::middleware(['auth'])->group(function () use ($path) {
    Route::get('/logout', $path . '\AuthController@logout')->name('logout');
    Route::get('/', $path . '\DashboardController@index')->name('dashboard');
    //Product
    Route::prefix('products')->group(function () use ($path) {
        //Category
        Route::prefix('category')->group(function () use ($path) {
            Route::get('/', $path . '\Api\CategoryController@index')->name('category.index');
            Route::match(['get', 'post'], '/{type}/ajax-modal', $path . '\Api\CategoryController@ajaxModal');
            Route::post('/store', $path . '\Api\CategoryController@store');
            Route::post('/update', $path . '\Api\CategoryController@update');
        });
        //Products List
        Route::prefix('product-list')->group(function () use ($path) {
            Route::get('/', $path . '\Api\ProductController@index')->name('product.index');
            Route::match(['get', 'post'], '/{type}/ajax-modal', $path . '\Api\ProductController@ajaxModal');
            Route::post('/store', $path . '\Api\ProductController@store');
            Route::get('/detail/{id}', $path . '\Api\ProductController@show');
            Route::post('/update', $path . '\Api\ProductController@update');
            Route::delete('/delete/{id}', $path . '\Api\ProductController@destroy');
            Route::get('/select2/{type}', $path . '\Api\ProductController@select2');
        });
    });
    Route::prefix('transaction')->group(function () use ($path) {
        Route::prefix('orders')->group(function () use ($path) {
           Route::get('/', $path . '\Api\OrderController@index')->name('orders.index');
           Route::match(['get', 'post'], '/{type}/ajax-modal', $path . '\Api\OrderController@ajaxModal');
           Route::get('/detail/{id}', $path . '\Api\OrderController@show');
           Route::get('/select2/{type}', $path . '\Api\OrderController@select2');
        });
    });
    Route::prefix('people')->group(function () use ($path) {
        Route::prefix('supplier')->group(function () use ($path) {
            Route::get('/', $path . '\Api\SupplierController@index')->name('supplier.index');
            Route::match(['get', 'post'], '/{type}/ajax-modal', $path . '\Api\SupplierController@ajaxModal');
            Route::post('/store', $path . '\Api\SupplierController@store');
            Route::get('/detail/{id}', $path . '\Api\SupplierController@show');
            Route::post('/update', $path . '\Api\SupplierController@update');
            Route::delete('/delete/{id}', $path . '\Api\SupplierController@destroy');
        });
        Route::prefix('customer')->group(function () use ($path) {
            Route::get('/', $path . '\Api\CustomerController@index')->name('customer.index');
            Route::match(['get', 'post'], '/{type}/ajax-modal', $path . '\Api\CustomerController@ajaxModal');
            Route::post('/store', $path . '\Api\CustomerController@store');
            Route::get('/detail/{id}', $path . '\Api\CustomerController@show');
            Route::post('/update', $path . '\Api\CustomerController@update');
            Route::delete('/delete/{id}', $path . '\Api\CustomerController@destroy');
        });
    });
});
