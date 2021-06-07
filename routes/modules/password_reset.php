<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Password Reset Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')
    ->name('password.email');
Route::post('password/reset', 'Auth\ResetPasswordController@reset')
    ->name('password.update');
