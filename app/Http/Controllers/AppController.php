<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Enums\MobileRegex;
use Illuminate\Http\Request;
use App\Models\AvailableCountry;
use App\Http\Resources\OnboardingSettingsResource;

class AppController extends Controller
{
    public function getAppCountries()
    {
        $appCountries = AvailableCountry::active()->get()->map(function ($country) {
            return [
                'id' => $country->id,
                'name' => $country->name,
                'flag_url' => $country->flag_url,
                'country_code' => $country->country_code,
            ];
        });
        return responseSuccessData($appCountries);
    }

    public function getMobileCountries()
    {
        $mobileCountries = Country::get()->map(function ($country) {
            return [
                'id' => $country->id,
                'name' => $country->name,
                'flag_url' => $country->flag_url,
                'country_code' => $country->code,
                'mobile_code' => $country->phone_code,
                'mobile_regex' => MobileRegex::ALL_MOBILE_REGEX[$country->code] ?? '',
            ];
        });
        return responseSuccessData($mobileCountries);
    }

    public function getOnboardingSettings(Request $request)
    {
        return responseSuccessData(OnboardingSettingsResource::make([]));
    }

}