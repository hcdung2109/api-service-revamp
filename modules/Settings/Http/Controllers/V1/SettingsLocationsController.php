<?php

namespace Digisource\Settings\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Settings\Contracts\SettingLocationServiceFactory;
use Digisource\Settings\Services\V1\SettingLocationService;
use Illuminate\Http\Request;

class SettingsLocationsController extends Controller
{

    private SettingLocationService $settingLocationSerivce;

    public function __construct(SettingLocationServiceFactory $settingLocationServiceFactory)
    {
        $this->settingLocationSerivce = $settingLocationServiceFactory;
    }

    // START SETTINGS EXPECT LOCATION
    public function getCities(Request $request)
    {
        $countryId = $request->get('country_id', null);
        $this->data = $this->settingLocationSerivce->getCities($countryId);
        return $this->getResponse();
    }
// END SETTINGS EXPECT LOCATION

// START SETTINGS NATIONALS
    public function getCountries()
    {
        $this->data = $this->settingLocationSerivce->getCountries();
        return $this->getResponse();
    }
// END SETTINGS NATIONALS

// START SETTINGS DISTRICTS
    public function getDistricts($id)
    {
        $this->data = $this->settingLocationSerivce->getDistricts($id);
        return $this->getResponse();
    }
// END SETTINGS DISTRICTS

// START SETTINGS WARDS
    public function getWards($id)
    {
        $this->data = $this->settingLocationSerivce->getWards($id);
        return $this->getResponse();
    }
// END SETTINGS WARDS
}
