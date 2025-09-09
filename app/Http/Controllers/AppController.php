<?php

namespace App\Http\Controllers;

use App\Models\Settings;
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
        $mobileCountries = AvailableCountry::active()->get()->map(function ($country) {
            return [
                'id' => $country->id,
                'name' => $country->name,
                'flag_url' => $country->flag_url,
                'country_code' => $country->country_code,
                'mobile_code' => $country->mobile_code,
                'mobile_regex' => MobileRegex::ALL_MOBILE_REGEX[$country->country_code] ?? '',
            ];
        });
        return responseSuccessData($mobileCountries);
    }

    public function getOnboardingSettings(Request $request)
    {
        return responseSuccessData(OnboardingSettingsResource::make([]));
    }
}