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
        'prefix' => 'v1/vendors'
    ],
    function ($router) {

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
