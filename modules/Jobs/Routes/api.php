<?php

use Illuminate\Http\Request;
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
        'prefix' => 'v1/jobs'
    ],
    function ($router) {
        $router->get('/job-group', 'JobsController@get_job_group');//QUAN
        $router->post('/job-group', 'JobsController@create_job_group');//QUAN
        $router->post('/job-group/{uuid}', 'JobsController@update_job_group');//QUAN
        $router->get('/job-group/{uuid}', 'JobsController@get_job_group_by_id');//QUAN
        $router->delete('/job-group/{uuid}', 'JobsController@delete_job_group');//QUAN

        $router->get('/skills', 'JobsController@get_job_skills');//QUAN
        $router->post('/skills', 'JobsController@create_job_skills');//QUAN
        $router->post('/skills/{uuid}', 'JobsController@update_job_skills');//QUAN
        $router->get('/skills/{uuid}', 'JobsController@get_job_skills_by_id');//QUAN
        $router->delete('/skills/{uuid}', 'JobsController@delete_job_skills');//QUAN

        $router->get('/notes', 'JobsController@get_job_notes');//QUAN
        $router->post('/notes', 'JobsController@create_job_notes');//QUAN
        $router->post('/notes/{uuid}', 'JobsController@update_job_notes');//QUAN
        $router->get('/notes/{uuid}', 'JobsController@get_job_notes_by_id');//QUAN
        $router->delete('/notes/{uuid}', 'JobsController@delete_job_notes');//QUAN

        $router->get('/job-stages', 'JobsController@get_job_stages');//QUAN
        $router->post('/job-stages', 'JobsController@create_job_stages');//QUAN
        $router->post('/job-stages/{uuid}', 'JobsController@update_job_stages');//QUAN
        $router->get('/job-stages/{uuid}', 'JobsController@get_job_stages_by_id');//QUAN
        $router->delete('/job-stages/{uuid}', 'JobsController@delete_job_stages');//QUAN

        $router->get('/job-candidate-stages', 'JobsController@get_job_candidate_stages');//QUAN
        $router->post('/job-candidate-stages', 'JobsController@create_job_candidate_stages');//QUAN
        $router->post('/job-candidate-stages/{uuid}', 'JobsController@update_job_candidate_stages');//QUAN
        $router->get('/job-candidate-stages/{uuid}', 'JobsController@get_job_candidate_stages_by_id');//QUAN
        $router->delete('/job-candidate-stages/{uuid}', 'JobsController@delete_job_candidate_stages');//QUAN

        $router->get('/job-commission', 'JobsController@get_job_commissions');//QUAN
        $router->post('/job-commission', 'JobsController@create_job_commissions');//QUAN
        $router->post('/job-commission/{uuid}', 'JobsController@update_job_commissions');//QUAN
        $router->get('/job-commission/{uuid}', 'JobsController@get_job_commissions_by_id');//QUAN
        $router->delete('/job-commission/{uuid}', 'JobsController@delete_job_commissions');//QUAN

        $router->get('/job-hiring-team', 'JobsController@get_job_hiring_team');//QUAN
        $router->post('/job-hiring-team', 'JobsController@create_job_hiring_team');//QUAN
        $router->post('/job-hiring-team/{uuid}', 'JobsController@update_job_hiring_team');//QUAN
        $router->get('/job-hiring-team/{uuid}', 'JobsController@get_job_hiring_team_by_id');//QUAN
        $router->delete('/job-hiring-team/{uuid}', 'JobsController@delete_job_hiring_team');//QUAN
    }
);
