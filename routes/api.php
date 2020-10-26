<?php

use Illuminate\Support\Facades\Route;

Route::post('login', 'App\Http\Controllers\Users\UserController@login');
Route::post('register', 'App\Http\Controllers\Users\UserController@register');
