<?php

use Illuminate\Support\Facades\Route;

Route::post('login', 'App\Http\Controllers\Api\Users\UserController@login');
Route::post('register', 'App\Http\Controllers\Api\Users\UserController@register');
Route::post('refreshtoken', 'App\Http\Controllers\Api\Users\UserController@refreshToken');
Route::get('/unauthorized', 'App\Http\Controllers\Api\Users\UserController@unauthorized');

Route::group(['middleware' => ['CheckClientCredentials', 'auth:api']], function () {
    Route::post('logout', 'App\Http\Controllers\Api\Users\UserController@logout');
    Route::get('/user/profile', 'App\Http\Controllers\Api\Users\UserController@profile');
});
