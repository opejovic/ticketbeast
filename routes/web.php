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

Route::get('concerts/{concert}', 'ConcertsController@show')->name('concerts.show');
Route::post('concerts/{concert}/orders', 'ConcertOrdersController@store')->name('orders.store');
Route::get('orders/{confirmationNumber}', 'OrdersController@show');

Route::get('/login', 'Auth\LoginController@showLoginForm');
Route::post('/login', 'Auth\LoginController@login')->name('login');
Route::post('/logout', 'Auth\LoginController@logout')->name('auth.logout');

Route::group(['middleware' => 'auth'], function () {
	Route::get('/backstage/concerts/new', 'Backstage\ConcertsController@create');
});