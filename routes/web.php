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
    Route::prefix('category')->group(function () use ($path) {
        Route::get('/', $path . '\Api\CategoryController@index')->name('category.index');
    });
});
