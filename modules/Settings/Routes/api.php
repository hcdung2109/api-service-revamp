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
use Illuminate\Support\Facades\Route;

$middlewares = ['api'];
Route::group(
    [
        'middleware' => $middlewares,
        'prefix' => 'v1/settings'
    ],
    function ($router) {
        $router->get('/global-skills', 'SettingsGlobalController@getSkills');
        $router->post('/global-skills', 'SettingsGlobalController@createSkills');
        $router->post('/global-skills/{uuid}', 'SettingsGlobalController@updateSkills');
        $router->get('/global-skills/{uuid}', 'SettingsGlobalController@getSkillsById');
        $router->delete('/global-skills/{uuid}', 'SettingsGlobalController@deleteSkills');

        $router->get('/global-countrys', 'SettingsLocationsController@getCountries');
        $router->get('/global-citys', 'SettingsLocationsController@getCities');
        $router->get('/global-districts/{id}', 'SettingsLocationsController@getDistricts');
        $router->get('/global-wards/{id}', 'SettingsLocationsController@getWards');

        $router->get('/candidate-sources', 'SettingsCandidateController@getCandidateSources');
        $router->post('/candidate-sources', 'SettingsCandidateController@createCandidateSources');
        $router->post('/candidate-sources/{uuid}', 'SettingsCandidateController@updateCandidateSources');
        $router->get('/candidate-sources/{uuid}', 'SettingsCandidateController@getCandidateSourcesById');
        $router->delete('/candidate-sources/{uuid}', 'SettingsCandidateController@deleteCandidateSources');

        $router->get('/candidate-levels', 'SettingsCandidateController@getCandidateLevels');
        $router->post('/candidate-levels', 'SettingsCandidateController@createCandidateLevels');
        $router->post('/candidate-levels/{uuid}', 'SettingsCandidateController@updateCandidateLevels');
        $router->get('/candidate-levels/{uuid}', 'SettingsCandidateController@getCandidateLevelsById');
        $router->delete('/candidate-levels/{uuid}', 'SettingsCandidateController@deleteCandidateLevels');

        $router->get('/company-industries', 'SettingsCompanyController@getCompanyIndustries');
        $router->post('/company-industries', 'SettingsCompanyController@createCompanyIndustries');
        $router->post('/company-industries/{uuid}', 'SettingsCompanyController@updateCompanyIndustries');
        $router->get('/company-industries/{uuid}', 'SettingsCompanyController@getCompanyIndustriesById');
        $router->delete('/company-industries/{uuid}', 'SettingsCompanyController@deleteCompanyIndustries');

        $router->get('/job-type', 'SettingsJobController@getJobTypes');
        $router->post('/job-type', 'SettingsJobController@createJobTypes');
        $router->post('/job-type/{uuid}', 'SettingsJobController@updateJobTypes');
        $router->get('/job-type/{uuid}', 'SettingsJobController@getJobTypesById');
        $router->delete('/job-type/{uuid}', 'SettingsJobController@deleteJobTypes');

        $router->get('/job-categories', 'SettingsJobController@getJobCategory');
        $router->post('/job-categories', 'SettingsJobController@createJobCategory');
        $router->post('/job-categories/{uuid}', 'SettingsJobController@updateJobCategory');
        $router->get('/job-categories/{uuid}', 'SettingsJobController@getJobCategoryById');
        $router->delete('/job-categories/{uuid}', 'SettingsJobController@deleteJobCategory');

        $router->get('/job-salary-ranges', 'SettingsJobController@getJobSalaryRanges');
        $router->post('/job-salary-ranges', 'SettingsJobController@createJobSalaryRanges');
        $router->post('/job-salary-ranges/{uuid}', 'SettingsJobController@updateJobSalaryRanges');
        $router->get('/job-salary-ranges/{uuid}', 'SettingsJobController@getJobSalaryRangesById');
        $router->delete('/job-salary-ranges/{uuid}', 'SettingsJobController@deleteJobSalaryRanges');

        $router->get('/company-sources', 'SettingsCompanyController@getCompanySources');
        $router->post('/company-sources', 'SettingsCompanyController@createCompanySources');
        $router->post('/company-sources/{uuid}', 'SettingsCompanyController@updateCompanySources');
        $router->get('/company-sources/{uuid}', 'SettingsCompanyController@getCompanySourcesById');
        $router->delete('/company-sources/{uuid}', 'SettingsCompanyController@deleteCompanySources');

        $router->get('/organization-role', 'SettingsOrganizationController@getRole');
        $router->post('/organization-role', 'SettingsOrganizationController@createRole');
        $router->post('/organization-role/{uuid}', 'SettingsOrganizationController@updateRole');
        $router->get('/organization-role/{uuid}', 'SettingsOrganizationController@getRoleById');
        $router->delete('/organization-role/{uuid}', 'SettingsOrganizationController@deleteRole');

        $router->get('/organization-department', 'SettingsOrganizationController@getDepartment');
        $router->get('/organization-department-all', 'SettingsOrganizationController@getDepartmentAll');
        $router->post('/organization-department', 'SettingsOrganizationController@createDepartment');
        $router->post('/organization-department/{uuid}', 'SettingsOrganizationController@updateDepartment');
        $router->get('/organization-department/{uuid}', 'SettingsOrganizationController@getDepartmentById');
        $router->delete('/organization-department/{uuid}', 'SettingsOrganizationController@deleteDepartment');

        $router->get('/user', 'SettingsUserController@get_user');
        $router->post('/user', 'SettingsUserController@create_user');
        $router->post('/user/{uuid}', 'SettingsUserController@update_user');
        $router->get('/user/{uuid}', 'SettingsUserController@get_user_by_id');
        $router->delete('/user/{uuid}', 'SettingsUserController@delete_user');

        $router->get('/user-department', 'SettingsUserController@get_user_department');
        $router->post('/user-department', 'SettingsUserController@create_user_department');
        $router->post('/user-department/{uuid}', 'SettingsUserController@update_user_department');
        $router->get('/user-department/{uuid}', 'SettingsUserController@get_user_department_by_id');
        $router->delete('/user-department/{uuid}', 'SettingsUserController@delete_user_department');

        $router->get('/vendor-commission-metric', 'SettingsVendorController@get_vendor_commission_metric');
        $router->post('/vendor-commission-metric', 'SettingsVendorController@create_vendor_commission_metric');
        $router->post('/vendor-commission-metric/{uuid}', 'SettingsVendorController@update_vendor_commission_metric');
        $router->get('/vendor-commission-metric/{uuid}', 'SettingsVendorController@get_vendor_commission_metric_by_id');
        $router->delete('/vendor-commission-metric/{uuid}', 'SettingsVendorController@delete_vendor_commission_metric');

        $router->get('/organization-permission', 'SettingsOrganizationController@get_permission');
        $router->post('/organization-permission/{uuid}', 'SettingsOrganizationController@update_permission');

    }
);
