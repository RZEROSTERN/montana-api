<?php

use Illuminate\Support\Facades\Route;

Route::post('login', 'App\Http\Controllers\Users\UserController@login');
Route::post('register', 'App\Http\Controllers\Users\UserController@register');
Route::post('refreshtoken', 'App\Http\Controllers\Users\UserController@refreshToken');
Route::get('/unauthorized', 'App\Http\Controllers\Users\UserController@unauthorized');

Route::group(['middleware' => ['CheckClientCredentials','auth:api']], function() {
    Route::post('logout', 'App\Http\Controllers\Users\UserController@logout');
    Route::get('/user/profile', 'App\Http\Controllers\Users\UserController@profile');
});
