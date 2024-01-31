<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Digisource\Career\Http\Controllers\V1\CareerSitesController;

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

Route::group([
    'middleware' => $middlewares,
    'prefix' => 'v1/career-sites',
], function () {
    Route::get('/career-site', [CareerSitesController::class, 'get_career_site']);
    Route::post('/career-site', [CareerSitesController::class, 'create_career_site']);
    Route::post('/career-site/{uuid}', [CareerSitesController::class, 'update_career_site']);
    Route::get('/career-site/{uuid}', [CareerSitesController::class, 'get_career_site_by_id']);
    Route::delete('/career-site/{uuid}', [CareerSitesController::class, 'delete_career_site']);

    Route::get('/social', [CareerSitesController::class, 'get_social']);
    Route::post('/social', [CareerSitesController::class, 'create_social']);
    Route::post('/social/{uuid}', [CareerSitesController::class, 'update_social']);
    Route::get('/social/{uuid}', [CareerSitesController::class, 'get_social_by_id']);
    Route::delete('/social/{uuid}', [CareerSitesController::class, 'delete_social']);
});
