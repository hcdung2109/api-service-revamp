<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Digisource\Notifications\Http\Controllers\V1\NotificationsController;

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
    'prefix' => 'v1/notifications'
], function ($router) {
    $router->get('/notification', [NotificationsController::class, 'get_notification_list']); // QUAN
    $router->get('/notification/{uuid}', [NotificationsController::class, 'get_notification_detail']); // QUAN
    $router->get('/notification-seen/{uuid}', [NotificationsController::class, 'post_notification_seen']); // QUAN
});
