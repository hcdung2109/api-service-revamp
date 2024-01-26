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
        'prefix' => 'v1/candidates'
    ],
    function ($router) {
        $router->get('/candidate/{uuid}', 'CandidateController@getCandidateById');
        $router->get('/candidate', 'CandidateController@getCandidate');

        $router->post('/create-candidate', 'CandidateController@createCandidate');
        $router->post('/candidate/{uuid}', 'CandidateController@updateCandidate');
        $router->delete('/candidate/{uuid}', 'CandidateController@destroy');
        $router->get('/expect-locations', 'CandidateController@getExpectLocation');

        $router->get('/global-skills', 'CandidateController@getSkills');

        $router->get('/candidate-sources', 'CandidateController@getCandidateSources');
        $router->get('/candidate-levels', 'CandidateController@getCandidateLevels');

        $router->get('/skills', 'CandidateCommonController@getCandidateSkills');
        $router->post('/skills', 'CandidateCommonController@createCandidateSkills');
        $router->post('/skills/{uuid}', 'CandidateCommonController@updateCandidateSkills');
        $router->get('/skills/{uuid}', 'CandidateCommonController@getCandidateSkillsById');
        $router->delete('/skills/{uuid}', 'CandidateCommonController@deleteCandidateSkills');

        $router->get('/notes', 'CandidateCommonController@getCandidateNotes');
        $router->post('/notes', 'CandidateCommonController@createCandidateNotes');
        $router->post('/notes/{uuid}', 'CandidateCommonController@updateCandidateNotes');
        $router->get('/notes/{uuid}', 'CandidateCommonController@getCandidateNotesById');
        $router->delete('/notes/{uuid}', 'CandidateCommonController@deleteCandidateNotes');

        $router->get('/resume', 'CandidateCommonController@getCandidateResume');
        $router->post('/resume', 'CandidateCommonController@createCandidateResume');
        $router->delete('/resume/{uuid}', 'CandidateCommonController@deleteCandidateResume');

        $router->get('/candidate-soft-column', 'CandidateCommonController@candidateSortColumn');
        $router->get('/candidate-soft-by', 'CandidateCommonController@candidateSortBy');

        $router->get('/candidate-apply-to-job', 'CandidateCommonController@getCandidateApplyToJob');
        $router->post('/add-candidate-apply-to-job', 'CandidateCommonController@addCandidateApplyToJob');

        $router->get('/candidate-get-stage-by-job', 'CandidateCommonController@getStageByJob');
        $router->post('/candidate-add-interview', 'CandidateController@createCandidateInterview');

        $router->post('/add-candidate-followed', 'CandidateCommonController@addCandidateFollowed');
        $router->delete('/remove-candidate-followed/{uuid}', 'CandidateCommonController@removeCandidateFollowed');
    }
);

