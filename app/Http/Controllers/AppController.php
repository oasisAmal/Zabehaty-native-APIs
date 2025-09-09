<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Models\AvailableCountry;

class AppController extends Controller
{
    public function getAvailableCountries()
    {
        $availableCountries = AvailableCountry::active()->get()->map(function ($country) {
            return [
                'id' => $country->id,
                'name' => $country->name,
                'flag_url' => $country->flag_url,
                'country_code' => $country->country_code,
            ];
        });
        return responseSuccessData($availableCountries);
    }

    public function getAuthOptions()
    {
        $authOptions = Settings::where('key', 'auth_options')->first()->value;
        return responseSuccessData($authOptions);
    }
}