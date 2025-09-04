<?php

namespace App\Http\Controllers;

class AppController extends Controller
{
    public function getAvailableCountries()
    {
        return responseSuccessData(AppCountries::getValues(), 200);
    }
}