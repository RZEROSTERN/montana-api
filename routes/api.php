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

    Route::post('/teams', 'App\Http\Controllers\Api\Teams\TeamsController@createTeam');
    Route::get('/teams', 'App\Http\Controllers\Api\Teams\TeamsController@getTeamsByUser');
    Route::get('/teams/{id}', 'App\Http\Controllers\Api\Teams\TeamsController@getTeamById');
    Route::put('/teams/{id}', 'App\Http\Controllers\Api\Teams\TeamsController@updateTeam');
    Route::delete('/teams/{id}', 'App\Http\Controllers\Api\Teams\TeamsController@deleteTeam');
});
