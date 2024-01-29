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
        'prefix' => 'v1/organizations'
    ],
    function ($router) {
         $router->get('/organization-manager', 'OrganizationsController@get_organization_manager');

         $router->get('/organization', 'OrganizationsController@get_organization_member');
         $router->post('/organization', 'OrganizationsController@create_organization_member');
         $router->post('/organization/:uuid', 'OrganizationsController@update_organization_member');
         $router->get('/organization/:uuid', 'OrganizationsController@get_organization_by_id_member');
         $router->delete('/organization/:uuid', 'OrganizationsController@delete_organization_member');

         $router->post('/organization-password/:uuid', 'OrganizationsController@update_organization_password_member');

         $router->get('/organization-org-chart', 'OrganizationsController@get_organization_org_chart');
    }
);
