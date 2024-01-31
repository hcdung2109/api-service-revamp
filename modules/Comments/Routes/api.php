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
        'middleware' => $middlewares,
        'prefix' => 'v1/comments'
    ],
    function ($router) {
        $router->get('/comment', 'CommentsController@get_comments');
        $router->post('/comment', 'CommentsController@create_comments');
        $router->post('/comment/:uuid', 'CommentsController@update_comments');
        $router->get('/comment/:uuid', 'CommentsController@get_comments_by_id');
        $router->delete('/comment/:uuid', 'CommentsController@delete_comments');
    }
);
