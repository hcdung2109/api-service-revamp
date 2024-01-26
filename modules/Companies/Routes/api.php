<?php


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
        'middleware' => $middlewares,
        'prefix' => 'v1/companies'
    ],
    function ($router) {
        $router->get('/company-industries', 'CompaniesController@getCompanyIndustries');
    }
);