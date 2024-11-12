<?php

namespace App\Http\Controllers\Public;

use App\Models\AppAwards;
use App\Models\AppCountries;
use App\Models\AppLanguages;
use App\Models\AppDepartements;
use App\Http\Controllers\Controller;
use App\Http\Collections\AwardCollection;

class AttributesController extends Controller
{
    /**
     ** Get Awards
     *
     * @return void
     */
    public function getAwards() 
    {
        $publicAwards = AppAwards::where('is_public', true)
            ->orderBy('label')
            ->get()
            ->map(function ($award) {
                return AwardCollection::render_public_award($award);
            });
        
        return response()->json([
            'public_awards' => $publicAwards,
            'message' => 'Awards loaded.',
        ], 200);
    }

    public function getLanguages() 
    {
        $language = AppLanguages::where('is_public', true)->get();
        return response()->json([
            'language' => $language,
            'message' => 'Language loaded.',
        ], 200);
    }

    public function getCountries() 
    {
        $countries = AppCountries::where('is_public', true)->get();
        return response()->json([
            'countries' => $countries,
            'message' => 'Countries loaded.',
        ], 200);
    }

    public function getDepartements()
    {
        $departements = AppDepartements::where('is_public', true)->orderBy('label')->get();
        
        return response()->json([
            'departements' => $departements,
            'message' => 'Departements loaded.',
        ], 200);
    }

    
}
