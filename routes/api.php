<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Users\UserController;

Route::post('login',  [UserController::class, 'login']);
Route::post('register', 'App\Http\Controllers\Api\Users\UserController@register');
Route::get('/unauthorized', 'App\Http\Controllers\Api\Users\UserController@unauthorized');

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('logout', 'App\Http\Controllers\Api\Users\UserController@logout');
    Route::get('/user/profile', 'App\Http\Controllers\Api\Users\UserController@profile');
    Route::post('/user/profile', 'App\Http\Controllers\Api\Users\UserController@createProfile');

    Route::post('/teams', 'App\Http\Controllers\Api\Teams\TeamsController@createTeam');
    Route::get('/teams', 'App\Http\Controllers\Api\Teams\TeamsController@getTeamsByUser');
    Route::get('/teams/{id}', 'App\Http\Controllers\Api\Teams\TeamsController@getTeamById');
    Route::put('/teams/{id}', 'App\Http\Controllers\Api\Teams\TeamsController@updateTeam');
    Route::delete('/teams/{id}', 'App\Http\Controllers\Api\Teams\TeamsController@deleteTeam');

    Route::get('/teams/{id}/members', 'App\Http\Controllers\Api\Teams\TeamMemberController@getTeamMembers');
    Route::post('/teams/members/add', 'App\Http\Controllers\Api\Teams\TeamMemberController@addUserToTeam');
    Route::post('/teams/members/drop', 'App\Http\Controllers\Api\Teams\TeamMemberController@dropUserFromTeam');

    Route::get('/leagues', 'App\Http\Controllers\Api\Leagues\LeaguesController@getAllLeagues');
    Route::post('/leagues', 'App\Http\Controllers\Api\Leagues\LeaguesController@createLeague');
    Route::put('/leagues', 'App\Http\Controllers\Api\Leagues\LeaguesController@updateLeague');
    Route::delete('/leagues', 'App\Http\Controllers\Api\Leagues\LeaguesController@deleteLeague');
});
