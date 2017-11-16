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
 * Estabelecimento routes
 */
Route::get('/estabelecimentos', 'EstabelecimentoController@index');
Route::get('/estabelecimentos/{estabelecimento}', 'EstabelecimentoController@show');

/**
 * Product routes
 */
Route::get('/products', 'ProductController@index');
Route::get('/products/{product}', 'ProductController@show');

/**
 * Produto routes
 */
Route::get('/produtos', 'ProdutoController@index');
Route::get('/produtos/{produto}', 'ProdutoController@show');

/**
 * Nfce routes
 */
Route::get('/nfce/{key}', 'NfceController@show');
Route::get('/nfce_integrate_all', 'NfceController@integrate_all');

/**
 * Nota routes
 */
Route::get('/nota/{key}', 'NotaController@store_nfce_data');