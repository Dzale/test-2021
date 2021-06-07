<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| TestController Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all routes for TestController. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'tests'], function () {
});

Route::apiResource('/tests', 'TestController', [
    'parameters' => [
        'tests' => 'test',
    ],
    'only' => [
        'index','show','store','update','destroy'
    ]
]);
