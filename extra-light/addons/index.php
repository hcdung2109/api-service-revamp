<?php

require 'vendor/autoload.php';

use Buki\Router\Router;

$router = new Router([
    'paths' => [
        'controllers' => 'app/controllers',
        'middlewares' => 'app/middlewares',
    ],
    'namespaces' => [
        'controllers' => 'AppLight\Controllers',
        'middlewares' => 'AppLight\Middlewares',
    ],
]);
$router->group('api', function ($router) use ($appSession) {

//    $router->post('/login', 'AuthController@login');
//
//    $router->post('/register', 'AuthController@register');
//
//    $router->post('/forgot-password', 'AuthController@forgot_password');
//
//    $router->post('/verify-user-code', 'AuthController@verify_user_code');
//
//    $router->post('/change-password', 'AuthController@change_password');

    $router->group('settings', function ($router) {

//        $router->get('/global-skills', 'SettingsGlobalController@get_skills');
//        $router->post('/global-skills', 'SettingsGlobalController@create_skills');
//        $router->post('/global-skills/:uuid', 'SettingsGlobalController@update_skills');
//        $router->get('/global-skills/:uuid', 'SettingsGlobalController@get_skills_by_id');
//        $router->delete('/global-skills/:uuid', 'SettingsGlobalController@delete_skills');

/*        $router->get('/global-countrys', 'SettingsLocationsController@get_countrys');
        $router->get('/global-citys', 'SettingsLocationsController@get_citis');
        $router->get('/global-districts/:id', 'SettingsLocationsController@get_districts');
        $router->get('/global-wards/:id', 'SettingsLocationsController@get_wards');*/

        //$router->get('/vendor-type', 'SettingsVendorController@get_vendor_type');
        //$router->post('/vendor-type', 'SettingsVendorController@create_vendor_type');
        //$router->post('/vendor-type/:uuid', 'SettingsVendorController@update_vendor_type');
        //$router->get('/vendor-type/:uuid', 'SettingsVendorController@get_vendor_type_by_id');
        //$router->delete('/vendor-type/:uuid', 'SettingsVendorController@delete_vendor_type');

        //$router->get('/vendor-commission-type', 'SettingsVendorController@get_vendor_commission_type');
        //$router->post('/vendor-commission-type', 'SettingsVendorController@create_vendor_commission_type');
        //$router->post('/vendor-commission-type/:uuid', 'SettingsVendorController@update_vendor_commission_type');
        //$router->get('/vendor-commission-type/:uuid', 'SettingsVendorController@get_vendor_commission_type_by_id');
        //$router->delete('/vendor-commission-type/:uuid', 'SettingsVendorController@delete_vendor_commission_type');

        //$router->get('/vendor-commission-kpis', 'SettingsVendorController@get_vendor_commission_kpis');
        //$router->post('/vendor-commission-kpis', 'SettingsVendorController@create_vendor_commission_kpis');
        //$router->post('/vendor-commission-kpis/:uuid', 'SettingsVendorController@update_vendor_commission_kpis');
        //$router->get('/vendor-commission-kpis/:uuid', 'SettingsVendorController@get_vendor_commission_kpis_by_id');
        //$router->delete('/vendor-commission-kpis/:uuid', 'SettingsVendorController@delete_vendor_commission_kpis');

        // $router->get('/job-type', 'SettingsJobController@get_job_types');
        // $router->post('/job-type', 'SettingsJobController@create_job_types');
        // $router->post('/job-type/:uuid', 'SettingsJobController@update_job_types');
        // $router->get('/job-type/:uuid', 'SettingsJobController@get_job_types_by_id');
        // $router->delete('/job-type/:uuid', 'SettingsJobController@delete_job_types');

        // $router->get('/job-categories', 'SettingsJobController@get_job_category');
        // $router->post('/job-categories', 'SettingsJobController@create_job_category');
        // $router->post('/job-categories/:uuid', 'SettingsJobController@update_job_category');
        // $router->get('/job-categories/:uuid', 'SettingsJobController@get_job_category_by_id');
        // $router->delete('/job-categories/:uuid', 'SettingsJobController@delete_job_category');

        // $router->get('/job-salary-ranges', 'SettingsJobController@alar');
        // $router->post('/job-salary-ranges', 'SettingsJobController@create_job_salary_ranges');
        // $router->post('/job-salary-ranges/:uuid', 'SettingsJobController@update_job_salary_ranges');
        // $router->get('/job-salary-ranges/:uuid', 'SettingsJobController@get_job_salary_ranges_by_id');
        // $router->delete('/job-salary-ranges/:uuid', 'SettingsJobController@delete_job_salary_ranges');

        // $router->get('/company-industries', 'SettingsCompanyController@get_company_industries');
        // $router->post('/company-industries', 'SettingsCompanyController@create_company_industries');
        // $router->post('/company-industries/:uuid', 'SettingsCompanyController@update_company_industries');
        // $router->get('/company-industries/:uuid', 'SettingsCompanyController@get_company_industries_by_id');
        // $router->delete('/company-industries/:uuid', 'SettingsCompanyController@delete_company_industries');

        // $router->get('/company-sources', 'SettingsCompanyController@get_company_sources');
        // $router->post('/company-sources', 'SettingsCompanyController@create_company_sources');
        // $router->post('/company-sources/:uuid', 'SettingsCompanyController@update_company_sources');
        // $router->get('/company-sources/:uuid', 'SettingsCompanyController@get_company_sources_by_id');
        // $router->delete('/company-sources/:uuid', 'SettingsCompanyController@delete_company_sources');

//        $router->get('/candidate-sources', 'SettingsCandidateController@get_candidate_sources');
//        $router->post('/candidate-sources', 'SettingsCandidateController@create_candidate_sources');
//        $router->post('/candidate-sources/:uuid', 'SettingsCandidateController@update_candidate_sources');
//        $router->get('/candidate-sources/:uuid', 'SettingsCandidateController@get_candidate_sources_by_id');
//        $router->delete('/candidate-sources/:uuid', 'SettingsCandidateController@delete_candidate_sources');
//
//        $router->get('/candidate-levels', 'SettingsCandidateController@get_candidate_levels');
//        $router->post('/candidate-levels', 'SettingsCandidateController@create_candidate_levels');
//        $router->post('/candidate-levels/:uuid', 'SettingsCandidateController@update_candidate_levels');
//        $router->get('/candidate-levels/:uuid', 'SettingsCandidateController@get_candidate_levels_by_id');
//        $router->delete('/candidate-levels/:uuid', 'SettingsCandidateController@delete_candidate_levels');

        // $router->get('/organization-role', 'SettingsOrganizationController@get_role');
        // $router->post('/organization-role', 'SettingsOrganizationController@create_role');
        // $router->post('/organization-role/:uuid', 'SettingsOrganizationController@update_role');
        // $router->get('/organization-role/:uuid', 'SettingsOrganizationController@get_role_by_id');
        // $router->delete('/organization-role/:uuid', 'SettingsOrganizationController@delete_role');

        //$router->get('/organization-department', 'SettingsOrganizationController@get_department');
        //$router->get('/organization-department-all', 'SettingsOrganizationController@get_department_all');
        //$router->post('/organization-department', 'SettingsOrganizationController@create_department');
        //$router->post('/organization-department/:uuid', 'SettingsOrganizationController@update_department');
        //$router->get('/organization-department/:uuid', 'SettingsOrganizationController@get_department_by_id');
        //$router->delete('/organization-department/:uuid', 'SettingsOrganizationController@delete_department');

        //$router->get('/user', 'SettingsUserController@get_user');
        //$router->post('/user', 'SettingsUserController@create_user');
        //$router->post('/user/:uuid', 'SettingsUserController@update_user');
        //$router->get('/user/:uuid', 'SettingsUserController@get_user_by_id');
        //$router->delete('/user/:uuid', 'SettingsUserController@delete_user');

        //$router->get('/user-department', 'SettingsUserController@get_user_department');
        //$router->post('/user-department', 'SettingsUserController@create_user_department');
        //$router->post('/user-department/:uuid', 'SettingsUserController@update_user_department');
        //$router->get('/user-department/:uuid', 'SettingsUserController@get_user_department_by_id');
        //$router->delete('/user-department/:uuid', 'SettingsUserController@delete_user_department');

        //$router->get('/vendor-commission-metric', 'SettingsVendorController@get_vendor_commission_metric');
        //$router->post('/vendor-commission-metric', 'SettingsVendorController@create_vendor_commission_metric');
        //$router->post('/vendor-commission-metric/:uuid', 'SettingsVendorController@update_vendor_commission_metric');
        //$router->get('/vendor-commission-metric/:uuid', 'SettingsVendorController@get_vendor_commission_metric_by_id');
        //$router->delete('/vendor-commission-metric/:uuid', 'SettingsVendorController@delete_vendor_commission_metric');

        //$router->get('/organization-permission', 'SettingsOrganizationController@get_permission');
        //$router->post('/organization-permission/:uuid', 'SettingsOrganizationController@update_permission');
    }, ['before' => 'AuthMiddlewares']);

    $router->group('candidates', function ($router) {

//        $router->get('/expect-locations', 'SettingsLocationsController@get_expect_location');

//        $router->get('/global-skills', 'SettingsGlobalController@get_skills');
//        $router->get('/candidate-sources', 'SettingsCandidateController@get_candidate_sources');
//        $router->get('/candidate-levels', 'SettingsCandidateController@get_candidate_levels');

//        $router->get('/candidate', 'CandidateController@get_candidate');
//        $router->post('/candidate', 'CandidateController@create_candidate');
//        $router->post('/candidate/:uuid', 'CandidateController@update_candidate');
//        $router->get('/candidate/:uuid', 'CandidateController@get_candidate_by_id');
//        $router->delete('/candidate/:uuid', 'CandidateController@delete_candidate');

//        $router->get('/resume', 'CandidateController@get_candidate_resume');
//        $router->post('/resume', 'CandidateController@create_candidate_resume');
//        $router->delete('/resume/:uuid', 'CandidateController@delete_candidate_resume');

//        $router->get('/skills', 'CandidateController@get_candidate_skills');
//        $router->post('/skills', 'CandidateController@create_candidate_skills');
//        $router->post('/skills/:uuid', 'CandidateController@update_candidate_skills');
//        $router->get('/skills/:uuid', 'CandidateController@get_candidate_skills_by_id');
//        $router->delete('/skills/:uuid', 'CandidateController@delete_candidate_skills');
//
//        $router->get('/notes', 'CandidateController@get_candidate_notes');
//        $router->post('/notes', 'CandidateController@create_candidate_notes');
//        $router->post('/notes/:uuid', 'CandidateController@update_candidate_notes');
//        $router->get('/notes/:uuid', 'CandidateController@get_candidate_notes_by_id');
//        $router->delete('/notes/:uuid', 'CandidateController@delete_candidate_notes');

//        $router->get('/candidate-soft-column', 'CandidateController@candidate_soft_column');
//        $router->get('/candidate-soft-by', 'CandidateController@candidate_soft_by');

//        $router->get('/candidate-apply-to-job', 'CandidateController@get_candidate_apply_to_job');
//        $router->post('/add-candidate-apply-to-job', 'CandidateController@add_candidate_apply_to_job');

//        $router->get('/candidate-get-stage-by-job', 'CandidateController@get_stage_by_job');
//        $router->post('/candidate-add-interview', 'CandidateController@candidate_add_interview');

        // $router->post('/add-candidate-followed', 'CandidateController@add_candidate_followed');
        // $router->delete('/remove-candidate-followed/:uuid', 'CandidateController@remove_candidate_followed');
    }, ['before' => 'AuthMiddlewares']);

    $router->group('companies', function ($router) {

        // $router->get('/company-industries', 'SettingsCompanyController@get_company_industries');

        $router->get('/company', 'CompanyController@get_company');
        $router->post('/company', 'CompanyController@create_company');
        $router->post('/company/:uuid', 'CompanyController@update_company');
        $router->get('/company/:uuid', 'CompanyController@get_company_by_id');
        $router->delete('/company/:uuid', 'CompanyController@delete_company');

        $router->get('/citys', 'SettingsLocationsController@get_expect_location');
        $router->get('/districts/:id', 'SettingsLocationsController@get_districts');
        $router->get('/wards/:id', 'SettingsLocationsController@get_wards');
        $router->get('/company-sources', 'SettingsCandidateController@get_candidate_sources');

        $router->get('/invoice', 'CompanyController@get_invoices');
        $router->post('/invoice', 'CompanyController@create_invoices');
        $router->post('/invoice/:uuid', 'CompanyController@update_invoices');
        $router->get('/invoice/:uuid', 'CompanyController@get_invoices_by_id');
        $router->delete('/invoice/:uuid', 'CompanyController@delete_invoices');

        //$router->get('/company-commissions', 'CompanyController@get_company_commissions');//QUAN
        //$router->post('/company-commissions', 'CompanyController@create_company_commissions');//QUAN
        //$router->post('/company-commissions/:uuid', 'CompanyController@update_company_commissions');//QUAN
        //$router->get('/company-commissions/:uuid', 'CompanyController@get_company_commissions_by_id');//QUAN
        //$router->delete('/company-commissions/:uuid', 'CompanyController@delete_company_commissions');//QUAN

        //$router->get('/notes', 'CompanyController@get_company_notes'); //QUAN
        //$router->post('/notes', 'CompanyController@create_company_notes'); //QUAN
        //$router->post('/notes/:uuid', 'CompanyController@update_company_notes'); //QUAN
        //$router->get('/notes/:uuid', 'CompanyController@get_job_company_by_id'); //QUAN
        //$router->delete('/notes/:uuid', 'CompanyController@delete_company_notes'); //QUAN

        $router->post('/add-companies-followed', 'CompanyController@add_companies_followed');
        $router->delete('/remove-companies-followed/:uuid', 'CompanyController@remove_companies_followed');

        $router->get('/companies-soft-column', 'CompanyController@companies_soft_column');
        $router->get('/companies-soft-by', 'CompanyController@companies_soft_by');
        $router->get('/companies-job-opening', 'CompanyController@get_job_opening');
        $router->get('/companies-candidate-in-job', 'CompanyController@get_candidate_in_jobs');

        $router->get('/companies-main-account', 'CompanyController@get_company_main_account');
        $router->post('/companies-main-account', 'CompanyController@create_company_main_account');
    }, ['before' => 'AuthMiddlewares']);

    $router->group('jobs', function ($router) {

        $router->get('/job', 'JobController@get_job');
        $router->post('/job', 'JobController@create_job');
        $router->post('/job/:uuid', 'JobController@update_job');
        $router->get('/job/:uuid', 'JobController@get_job_by_id');
        $router->delete('/job/:uuid', 'JobController@delete_job');

        $router->get('/citys', 'SettingsLocationsController@get_expect_location');
        $router->get('/departments', 'SettingsOrganizationController@get_department');
        $router->get('/companys', 'CompanyController@get_company');
        $router->get('/job-types', 'SettingsJobController@get_job_types');
        $router->get('/job-categories', 'SettingsJobController@get_job_category');

        //$router->get('/job-group', 'JobController@get_job_group');//QUAN
        //$router->post('/job-group', 'JobController@create_job_group');//QUAN
        //$router->post('/job-group/:uuid', 'JobController@update_job_group');//QUAN
        //$router->get('/job-group/:uuid', 'JobController@get_job_group_by_id');//QUAN
        //$router->delete('/job-group/:uuid', 'JobController@delete_job_group');//QUAN

        //$router->get('/job-group-permission', 'JobController@get_job_group_permission');//QUAN
        //$router->post('/job-group-permission', 'JobController@create_job_group_permission');//QUAN
        //$router->post('/job-group-permission/:uuid', 'JobController@update_job_group_permission');//QUAN
        //$router->get('/job-group-permission/:uuid', 'JobController@get_job_group_permission_by_id');//QUAN
        //$router->delete('/job-group-permission/:uuid', 'JobController@delete_job_group_permission');//QUAN

        //$router->get('/skills', 'JobController@get_job_skills');//QUAN
        //$router->post('/skills', 'JobController@create_job_skills');//QUAN
        //$router->post('/skills/:uuid', 'JobController@update_job_skills');//QUAN
        //$router->get('/skills/:uuid', 'JobController@get_job_skills_by_id');//QUAN
        //$router->delete('/skills/:uuid', 'JobController@delete_job_skills');//QUAN

        //$router->get('/notes', 'JobController@get_job_notes');//QUAN
        //$router->post('/notes', 'JobController@create_job_notes');//QUAN
        //$router->post('/notes/:uuid', 'JobController@update_job_notes');//QUAN
        //$router->get('/notes/:uuid', 'JobController@get_job_notes_by_id');//QUAN
        //$router->delete('/notes/:uuid', 'JobController@delete_job_notes');//QUAN

        //$router->get('/job-stages', 'JobController@get_job_stages');//QUAN
        //$router->post('/job-stages', 'JobController@create_job_stages');//QUAN
        //$router->post('/job-stages/:uuid', 'JobController@update_job_stages');//QUAN
        //$router->get('/job-stages/:uuid', 'JobController@get_job_stages_by_id');//QUAN
        //$router->delete('/job-stages/:uuid', 'JobController@delete_job_stages');//QUAN

        $router->get('/job-candidate-stages', 'JobController@get_job_candidate_stages');
        $router->post('/job-candidate-stages', 'JobController@create_job_candidate_stages');
        $router->post('/job-candidate-stages/:uuid', 'JobController@update_job_candidate_stages');
        $router->get('/job-candidate-stages/:uuid', 'JobController@get_job_candidate_stages_by_id');
        $router->delete('/job-candidate-stages/:uuid', 'JobController@delete_job_candidate_stages');

        //$router->get('/job-commission', 'JobController@get_job_commissions');//QUAN
        //$router->post('/job-commission', 'JobController@create_job_commissions');//QUAN
        //$router->post('/job-commission/:uuid', 'JobController@update_job_commissions');//QUAN
        //$router->get('/job-commission/:uuid', 'JobController@get_job_commissions_by_id');//QUAN
        //$router->delete('/job-commission/:uuid', 'JobController@delete_job_commissions');//QUAN

        $router->post('/add-job-followed', 'JobController@add_job_followed');
        $router->delete('/remove-job-followed/:uuid', 'JobController@remove_job_followed');

        $router->get('/job-soft-column', 'JobController@job_soft_column');
        $router->get('/job-soft-by', 'JobController@job_soft_by');

        $router->get('/job-hiring-team', 'JobController@get_job_hiring_team');
        $router->post('/job-hiring-team', 'JobController@create_job_hiring_team');
        $router->post('/job-hiring-team/:uuid', 'JobController@update_job_hiring_team');
        $router->get('/job-hiring-team/:uuid', 'JobController@get_job_hiring_team_by_id');
        $router->delete('/job-hiring-team/:uuid', 'JobController@delete_job_hiring_team');
    }, ['before' => 'AuthMiddlewares']);

    $router->group('vendors', function ($router) {

        // $router->get('/vendor-type', 'SettingsVendorController@get_vendor_type');

        // $router->get('/vendor', 'VendorController@get_vendor');
        // $router->post('/vendor', 'VendorController@create_vendor');
        // $router->post('/vendor/:uuid', 'VendorController@update_vendor');
        // $router->get('/vendor/:uuid', 'VendorController@get_vendor_by_id');
        // $router->delete('/vendor/:uuid', 'VendorController@delete_vendor');

        // $router->get('/citys', 'SettingsLocationsController@get_expect_location');
        // $router->get('/districts/:id', 'SettingsLocationsController@get_districts');
        // $router->get('/wards/:id', 'SettingsLocationsController@get_wards');
        // $router->get('/vendor-sources', 'SettingsCandidateController@get_candidate_sources');

        // $router->get('/notes', 'VendorController@get_vendor_notes');
        // $router->post('/notes', 'VendorController@create_vendor_notes');
        // $router->post('/notes/:uuid', 'VendorController@update_vendor_notes');
        // $router->get('/notes/:uuid', 'VendorController@get_vendor_notes_by_id');
        // $router->delete('/notes/:uuid', 'VendorController@delete_vendor_notes');

        // $router->post('/add-vendor-followed', 'VendorController@add_vendor_followed');
        // $router->delete('/remove-vendor-followed/:uuid', 'VendorController@remove_vendor_followed');

        // $router->get('/vendor-soft-column', 'VendorController@vendor_soft_column');
        // $router->get('/vendor-soft-by', 'VendorController@vendor_soft_by');

        // $router->get('/vendor-job-opening', 'VendorController@get_job_opening');
        // $router->get('/vendor-commissions', 'VendorController@get_vendor_commission');


        // $router->get('/vendor-main-account', 'VendorController@get_vendor_main_account');
        // $router->post('/vendor-main-account', 'VendorController@create_vendor_main_account');
    }, ['before' => 'AuthMiddlewares']);

    $router->group('comments', function ($router) {

        $router->get('/comment', 'CommentsController@get_comments');
        $router->post('/comment', 'CommentsController@create_comments');
        $router->post('/comment/:uuid', 'CommentsController@update_comments');
        $router->get('/comment/:uuid', 'CommentsController@get_comments_by_id');
        $router->delete('/comment/:uuid', 'CommentsController@delete_comments');
    }, ['before' => 'AuthMiddlewares']);

    //$router->group('profiles', function ($router) {

        //$router->get('/profile', 'UserController@get_profile_by_id');
        //$router->post('/profile/:uuid', 'UserController@update_profile');
    //}, ['before' => 'AuthMiddlewares']);

    $router->group('organizations', function ($router) {

        // $router->get('/organization-manager', 'OrganizationController@get_organization_manager');

        // $router->get('/organization', 'OrganizationController@get_organization_member');
        // $router->post('/organization', 'OrganizationController@create_organization_member');
        // $router->post('/organization/:uuid', 'OrganizationController@update_organization_member');
        // $router->get('/organization/:uuid', 'OrganizationController@get_organization_by_id_member');
        // $router->delete('/organization/:uuid', 'OrganizationController@delete_organization_member');

        // $router->post('/organization-password/:uuid', 'OrganizationController@update_organization_password_member');

        // $router->get('/organization-org-chart', 'OrganizationController@get_organization_org_chart');
    }, ['before' => 'AuthMiddlewares']);

    $router->group('dashboards', function ($router) {
        $router->get('/total', 'DashboardController@get_total');
        $router->get('/job-opening', 'DashboardController@get_job_opening');
        $router->get('/candidate-in-job', 'DashboardController@get_candidate_in_job');
    }, ['before' => 'AuthMiddlewares']);

    $router->group('calendars', function ($router) {
        $router->get('/calendar', 'CalendarController@get_calendar');
    }, ['before' => 'AuthMiddlewares']);

    $router->group('career-sites', function ($router) {

        $router->get('/career-site', 'CareerSiteController@get_career_site');
        $router->post('/career-site', 'CareerSiteController@create_career_site');
        $router->post('/career-site/:uuid', 'CareerSiteController@update_career_site');
        $router->get('/career-site/:uuid', 'CareerSiteController@get_career_site_by_id');
        $router->delete('/career-site/:uuid', 'CareerSiteController@delete_career_site');

        $router->get('/social', 'CareerSiteController@get_social');
        $router->post('/social', 'CareerSiteController@create_social');
        $router->post('/social/:uuid', 'CareerSiteController@update_social');
        $router->get('/social/:uuid', 'CareerSiteController@get_social_by_id');
        $router->delete('/social/:uuid', 'CareerSiteController@delete_social');
    }, ['before' => 'AuthMiddlewares']);

    $router->group('reports', function ($router) {
        $router->get('/report-jobs', 'ReportController@get_report_job');
        $router->get('/report-application-candidates', 'ReportController@get_report_application_candidates');
        $router->get('/report-source-candidates', 'ReportController@get_report_source');
        $router->get('/report-companies', 'ReportController@get_report_company');
        $router->get('/report-vendor', 'ReportController@get_report_vendor');
    }, ['before' => 'AuthMiddlewares']);

    $router->group('packages', function ($router) {
        $router->get('/package', 'PackageController@get_packages');
    }, ['before' => 'AuthMiddlewares']);

    $router->group('payments', function ($router) {
        $router->get('/banks', 'PaymentController@get_banks');
        $router->post('/banks', 'PaymentController@get_banks');
    }, ['before' => 'AuthMiddlewares']);

    $router->group('notifications', function ($router) {
        $router->get('/notification', 'NotificationController@get_notification_list');
        $router->get('/notification/:uuid', 'NotificationController@get_notification_detail');
        $router->get('/notification-seen/:uuid', 'NotificationController@post_notification_seen');
        $router->post('/notification-test', 'NotificationController@post_notification_test');
    }, ['before' => 'AuthMiddlewares']);
});
$router->run();
