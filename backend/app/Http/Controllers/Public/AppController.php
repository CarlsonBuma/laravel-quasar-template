<?php

namespace App\Http\Controllers\Public;

use App\Models\AppCountries;
use App\Models\AppLanguages;
use App\Http\Controllers\Controller;

class AppController extends Controller
{
    /**
     * Undocumented function
     *
     * @return void
     */
    public function loadLanguages() 
    {
        return response()->json([
            'language' => AppLanguages::where('is_public', true)->get(),
            'message' => 'Language loaded.',
        ], 200);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function loadCountries() 
    {
        return response()->json([
            'countries' => AppCountries::where('is_public', true)->get(),
            'message' => 'Countries loaded.',
        ], 200);
    }
}
