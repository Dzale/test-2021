<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all routes for user authentication. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'auth'], function ($router) {
    Route::get('/csrf-cookie', '\Laravel\Sanctum\Http\Controllers\CsrfCookieController@show');

    Route::post('/login', 'Auth\AuthController@login')
        ->middleware('throttle')
        ->name('auth.login');

    Route::post('/logout', 'Auth\AuthController@logout')->name('auth.logout');

    Route::group(['prefix' => '/tokens'], function () {
        Route::delete('/', 'Auth\TokenController@destroy')->name('auth.tokens.destroy');
    });

    Route::apiResource('/tokens', 'Auth\TokenController', [
        'only' => ['store']
    ]);
});
