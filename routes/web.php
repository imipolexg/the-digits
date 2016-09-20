<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// The preset authentication routes
// https://github.com/laravel/framework/blob/5.3/src/Illuminate/Routing/Router.php#L287
Auth::routes();

Route::get('auth/{provider}', 'Auth\SocialAuthController@redirectToProvider');
Route::get('auth/{provider}/callback', 'Auth\SocialAuthController@handleProviderCallback');

Route::get('/home', 'HomeController@index');

/* Contact AJAX routes */
Route::get('/contacts', 'ContactsController@index');
Route::post('/contacts', 'ContactsController@create');
Route::get('/contacts/{email}', 'ContactsController@find');
Route::patch('/contacts/{email}', 'ContactsController@update');
Route::delete('/contacts/{email}', 'ContactsController@delete');

