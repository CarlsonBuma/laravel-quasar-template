<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsApp extends Model
{
    use HasFactory;

    protected $table = 'public.analytics_app';

    protected $fillable = [
        'tag',
        'details',
        'visitors_per_day'
    ];
}
