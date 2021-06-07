<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| UserController Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all routes for UserController. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'users'], function () {
    Route::get('me', 'UserController@me')
        ->name('users.me');

    Route::put('me', 'UserController@update')
        ->name('users.update');

    Route::post('me/password', 'UserController@updatePassword')
        ->name('users.updatePassword');
});

Route::apiResource('/users', 'UserController', [
    'parameters' => [
        'users' => 'user',
    ],
    'only' => [
        'store'
    ]
]);
