<?php

use Digisource\Settings\Http\Controllers\V1\SettingsCandidateController;
use Digisource\Settings\Http\Controllers\V1\SettingsLocationsController;
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
        'prefix' => 'v1/vendors'
    ],
    function ($router) {
        $router->get('/vendor-type', 'VendorTypesController@getVendorType');
        $router->get('/vendor', 'VendorsController@getVendor');
        $router->get('/vendor/{uuid}', 'VendorsController@getVendorById');
        $router->post('/vendor', 'VendorsController@createVendor');
        $router->post('/vendor/{uuid}', 'VendorsController@updateVendor');
        $router->delete('/vendor/{uuid}', 'VendorsController@deleteVendor');

        $router->get('/citys', [SettingsLocationsController::class, 'getCities']);
        $router->get('/districts/{id}', [SettingsLocationsController::class, 'getDistricts']);
        $router->get('/wards/{id}', [SettingsLocationsController::class, 'getWards']);
        $router->get('/vendor-sources', [SettingsCandidateController::class, 'getCandidateSources']);

        $router->get('/notes', 'VendorsController@get_vendor_notes');
        $router->post('/notes', 'VendorsController@create_vendor_notes');
        $router->post('/notes/{uuid}', 'VendorsController@update_vendor_notes');
        $router->get('/notes/{uuid}', 'VendorsController@get_vendor_notes_by_id');
        $router->delete('/notes/{uuid}', 'VendorsController@delete_vendor_notes');

        $router->post('/add-vendor-followed', 'VendorsController@add_vendor_followed');
        $router->delete('/remove-vendor-followed/{uuid}', 'VendorsController@remove_vendor_followed');

        $router->get('/vendor-soft-column', 'VendorsController@vendor_soft_column');
        $router->get('/vendor-soft-by', 'VendorsController@vendor_soft_by');

        $router->get('/vendor-job-opening', 'VendorsController@get_job_opening');
        $router->get('/vendor-commissions', 'VendorsController@get_vendor_commission');


        $router->get('/vendor-main-account', 'VendorsController@get_vendor_main_account');
        $router->post('/vendor-main-account', 'VendorsController@create_vendor_main_account');
    }
);

Route::group(
    [
        'middleware' => $middlewares,
        'prefix' => 'v1/settings'
    ],
    function ($router) {
        $router->get('/vendor-type', 'VendorTypesController@get_vendor_type');
        $router->post('/vendor-type', 'VendorTypesController@create_vendor_type');
        $router->get('/vendor-type/{uuid}', 'VendorTypesController@get_vendor_type_by_id');
        $router->post('/vendor-type/{uuid}', 'VendorTypesController@update_vendor_type');
        $router->delete('/vendor-type/{uuid}', 'VendorTypesController@delete_vendor_type');

        $router->get('/vendor-commission-type', 'SettingsVendorController@get_vendor_commission_type');
        $router->post('/vendor-commission-type', 'SettingsVendorController@create_vendor_commission_type');
        $router->post('/vendor-commission-type/:uuid', 'SettingsVendorController@update_vendor_commission_type');
        $router->get('/vendor-commission-type/:uuid', 'SettingsVendorController@get_vendor_commission_type_by_id');
        $router->delete('/vendor-commission-type/:uuid', 'SettingsVendorController@delete_vendor_commission_type');

        $router->get('/vendor-commission-kpis', 'SettingsVendorController@get_vendor_commission_kpis');
        $router->post('/vendor-commission-kpis', 'SettingsVendorController@create_vendor_commission_kpis');
        $router->post('/vendor-commission-kpis/:uuid', 'SettingsVendorController@update_vendor_commission_kpis');
        $router->get('/vendor-commission-kpis/:uuid', 'SettingsVendorController@get_vendor_commission_kpis_by_id');
        $router->delete('/vendor-commission-kpis/:uuid', 'SettingsVendorController@delete_vendor_commission_kpis');
    }
);
