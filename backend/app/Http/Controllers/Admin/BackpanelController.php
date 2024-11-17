<?php

namespace App\Http\Controllers\Admin;

use App\Models\Users;
use App\Http\Controllers\Controller;

class BackpanelController extends Controller
{
    /**
     * Undocumented function
     *
     * @return void
     */
    public function loadDashboard()
    {
        return response()->json([
            'users' => Users::where('email_verified_at', '!=', null)->count()
        ], 200);
    }
}
