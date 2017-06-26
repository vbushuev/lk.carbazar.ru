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
Route::get('/home/accounts', 'HomeController@accounts')->name('accounts');
Route::get('/home/apikeys', 'HomeController@apikeys')->name('apikeys');
Route::get('/home/clients', 'HomeController@clients')->name('clients');

Route::get('/data', 'DataController@index')->name('data');
Route::get('/data/pdf', 'DataController@pdf')->name('pdf');
Route::get('/data/csv', 'DataController@csv')->name('csv');
Route::get('/data/vin', 'DataController@vin')->name('vin');

Route::get('/data/client/info', 'DataController@clientinfo')->name('client');
Route::get('/data/client/update', 'DataController@clientUpdate')->name('client');

Route::get('/data/account', 'DataController@acountinfo')->name('account');
Route::get('/data/account/list', 'DataController@acountlist')->name('accounts');
Route::get('/data/account/add', 'DataController@acountadd')->name('acountadd');
Route::get('/data/account/update', 'DataController@acountupdate')->name('acountupdate');

Route::get('/data/apikey', 'DataController@apikeyinfo')->name('apikey');
Route::get('/data/apikey/list', 'DataController@apikeylist')->name('apikeys');
Route::get('/data/apikey/add', 'DataController@apikeyadd')->name('apikeyadd');
Route::get('/data/apikey/update', 'DataController@apikeyupdate')->name('apikeyupdate');
Route::get('/data/apikey/remove', 'DataController@apikeyremove')->name('apikeyremove');

Route::get('/data/client', 'DataController@clientinfo')->name('client');
Route::get('/data/client/list', 'DataController@clientlist')->name('clients');
Route::get('/data/client/add', 'DataController@clientadd')->name('clientadd');
Route::get('/data/client/update', 'DataController@clientupdate')->name('clientupdate');
Route::get('/data/client/remove', 'DataController@clientremove')->name('clientremove');
