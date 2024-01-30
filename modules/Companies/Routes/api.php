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

        $router->get('/company', 'CompanyController@get_company');
        $router->post('/company', 'CompanyController@create_company');
        $router->post('/company/:uuid', 'CompanyController@update_company');
        $router->get('/company/:uuid', 'CompanyController@get_company_by_id');
        $router->delete('/company/:uuid', 'CompanyController@delete_company');

        $router->get('/company-commissions', 'CompaniesController@get_company_commissions');//QUAN
        $router->post('/company-commissions', 'CompaniesController@create_company_commissions');//QUAN
        $router->post('/company-commissions/{uuid}', 'CompaniesController@update_company_commissions');//QUAN
        $router->get('/company-commissions/{uuid}', 'CompaniesController@get_company_commissions_by_id');//QUAN
        $router->delete('/company-commissions/{uuid}', 'CompaniesController@delete_company_commissions');//QUAN

        $router->get('/notes', 'CompaniesController@get_company_notes'); //QUAN
        $router->post('/notes', 'CompaniesController@create_company_notes'); //QUAN
        $router->post('/notes/{uuid}', 'CompaniesController@update_company_notes'); //QUAN
        $router->get('/notes/{uuid}', 'CompaniesController@get_job_company_by_id'); //QUAN
        $router->delete('/notes/{uuid}', 'CompaniesController@delete_company_notes'); //QUAN

    }
);
