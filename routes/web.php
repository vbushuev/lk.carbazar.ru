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
Route::get('/data', 'DataController@index')->name('data');
Route::get('/data/pdf', 'DataController@pdf')->name('pdf');
Route::get('/data/csv', 'DataController@csv')->name('csv');
Route::get('/data/vin', 'DataController@vin')->name('vin');

Route::get('/data/client/info', 'DataController@clientinfo')->name('client');
Route::get('/data/client/update', 'DataController@clientUpdate')->name('client');
Route::get('/data/account', 'DataController@acountinfo')->name('account');
Route::get('/data/apikey', 'DataController@apikeyinfo')->name('apikey');
