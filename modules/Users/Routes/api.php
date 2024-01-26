<?php

use Illuminate\Support\Facades\Route;

/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/

$middlewares = ['api'];
Route::group(
    [
//        'prefix' => '{version}'
    ],
    function ($router) {
        $router->post('/forgot-password', 'AuthController@sendOTP');
        $router->post('/verify-user-code', 'AuthController@verifyOTP');
        $router->post('/change-password', 'AuthController@changePassword');
        $router->post('/login', 'AuthController@login');
        $router->get('/active/{key}', 'AuthController@active');
        $router->post('/register', 'AuthController@register');
    }
);

Route::group(
    [
        'middleware' => $middlewares,
        'prefix' => '{version}'
    ],
    function ($router) {
        $router->get('/details/{id}', 'AuthController@getaccount');
        $router->get('/logout', 'AuthController@logout');
    }
);
