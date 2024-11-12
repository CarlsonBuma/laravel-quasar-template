<?php

namespace App\Http\Controllers\Public;

use Carbon\Carbon;
use App\Models\User;
use App\Models\UserEntity;
use Illuminate\Http\Request;
use App\Models\AnalyticsApp;
use App\Http\Controllers\Controller;

class AnalyticsAppController extends Controller
{
    //* Taglist in DB
    public $tag_appVisits = 'app-visit';

    /**
     * Landing Page Analytics Stats
     *
     * @return void
     */
    public function getPublicStats() 
    {
        $avgVisitorsPerMonth = 0;
        $appVisitors = AnalyticsApp::where('tag', $this->tag_appVisits);

        if($appVisitors->exists()) {
            $startDate = Carbon::parse($appVisitors->min('created_at'))->format('Y-m');
            $today = Carbon::now()->format('Y-m');
            $periodInMonths = Carbon::parse($startDate)->diffInMonths(Carbon::parse($today));
            
            $totalVisitors = $appVisitors->sum('visitors_per_day');

            $avgVisitorsPerMonth = $periodInMonths > 0 
                ? $totalVisitors / $periodInMonths 
                : $totalVisitors;
        }

        $amountEntities = UserEntity::count();
        $amountUsers = User::count();

        return response()->json([
            'visitors' => intval($avgVisitorsPerMonth),
            'amount_entities' => $amountEntities,
            'amount_users' => $amountUsers,
            'message' => 'Success.',
        ], 200);
    }

    /**
     * Add new Visitor, according tag
     *
     * @param Request $request
     * @return void
     */
    public function addAppVisitor(Request $request)
    {
        $data = $request->validate([
            'tag' => ['required', 'string'],
        ]);
        
        $latestEntry = AnalyticsApp::where([
            'tag' => $data['tag']    
        ])->latest()->first();

        if ($latestEntry && $latestEntry->created_at->isToday()) {
            $latestEntry->visitors_per_day = $latestEntry->visitors_per_day + 1;
            $latestEntry->save();
        } else {
            AnalyticsApp::create([
                'visitors_per_day' => 1,
                'tag' => $data['tag']
            ]);
        }
    }
}
