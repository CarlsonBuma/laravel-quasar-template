<?php

namespace App\Http\Controllers\Public;

use App\Models\AppReleases;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContentController extends Controller
{
    /**
     * Undocumented function
     *
     * @param integer $index
     * @return void
     */
    public function loadIndexedReleases(Request $request)
    {
        $entriesPerRequest = 12;
        $data = $request->validate([
            'index' => ['required', 'numeric'],
        ]);
        
        $releases = AppReleases::orderBy('created_at', 'desc')
            ->skip($data['index'])
            ->take($entriesPerRequest + 1)
            ->get();
        
        // Check last entry
        $isLastEntry = $releases->count() <= $entriesPerRequest;
        if(!$isLastEntry) $releases->pop();
        
        return response()->json([
            'releases' => $releases,
            'is_last_entry' => $isLastEntry
        ], 200);
    }
}
