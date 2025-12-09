<?php

namespace Modules\DynamicCategories\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DynamicCategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return responseSuccessData([]);
    }
}
