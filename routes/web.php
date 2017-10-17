<?php

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

/**
 * Product routes
 */
Route::get('/products', 'ProductController@index')->middleware('cors');
Route::get('/products/create', 'ProductController@create')->middleware('cors');
Route::post('/products', 'ProductController@store')->middleware('cors');
Route::get('/products/{product}', 'ProductController@show')->middleware('cors');
Route::get('/products/{product}/edit', 'ProductController@edit')->middleware('cors');
Route::put('/products/{product}', 'ProductController@update')->middleware('cors');
Route::delete('/products/{product}', 'ProductController@destroy')->middleware('cors');

/**
 * Nfce routes
 */
Route::get('/nfce/{key}', 'NfceController@show');