<?php

namespace Digisource\Settings\Services\V1;


use Digisource\Settings\Contracts\SettingLocationServiceFactory;
use Digisource\Settings\Entities\Address;
use Digisource\Settings\Entities\Country;

class SettingLocationService implements SettingLocationServiceFactory
{
    public function __construct()
    {
    }

    public function getCities($countryId = null)
    {
        $cities = Address::query()
            ->select('id', 'name')
            ->where('status', 0)
            ->where('type', 'CITY');

        if ($countryId) {
            $cities->where('country_id', $countryId);
        }
        $cities = $cities->get();

        return ['cities' => $cities->toArray()];
    }
// END SETTINGS EXPECT LOCATION

// START SETTINGS NATIONALS
    public function getCountries()
    {
        $countries = Country::query()
            ->select('id', 'name')
            ->where('status', 0)
            ->where('active', 1)
            ->get();

        return ['countrys' => $countries->toArray()];
    }
// END SETTINGS NATIONALS

// START SETTINGS DISTRICTS
    public function getDistricts($id)
    {
        $districts = Address::query()
            ->select('id', 'name')
            ->where('status', 0)
            ->where('parent_id', $id)
            ->where('type', 'DIST')
            ->orderBy('name')
            ->get();

        return ['districts' => $districts->toArray()];
    }
// END SETTINGS DISTRICTS

// START SETTINGS WARDS
    public function getWards($id)
    {
        $wards = Address::query()
            ->select('id', 'name')
            ->where('status', 0)
            ->where('parent_id', $id)
            ->where('type', 'WARD')
            ->orderBy('name')
            ->get();

        return ['wards' => $wards->toArray()];
    }
}
