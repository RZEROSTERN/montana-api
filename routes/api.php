<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Users\UserController;

Route::post('login',  [UserController::class, 'login']);
Route::post('register', 'App\Http\Controllers\Api\Users\UserController@register');
Route::post('refreshtoken', 'App\Http\Controllers\Api\Users\UserController@refreshToken');
Route::get('/unauthorized', 'App\Http\Controllers\Api\Users\UserController@unauthorized');

Route::group(['middleware' => ['CheckClientCredentials', 'auth:api']], function () {
    Route::post('logout', 'App\Http\Controllers\Api\Users\UserController@logout');
    Route::get('/user/profile', 'App\Http\Controllers\Api\Users\UserController@profile');
    Route::post('/user/profile', 'App\Http\Controllers\Api\Users\UserController@createProfile');
});
