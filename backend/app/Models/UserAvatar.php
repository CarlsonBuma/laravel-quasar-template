<?php

namespace App\Models;

use App\Models\User;
use App\Models\AppCountries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserAvatar extends Model
{
    use HasFactory;

    protected $table = 'public.user_avatar';

    protected $fillable = [
        'user_id',
        'is_public',         // Flag
        'about',
        'contact',
        'location_id',
        'country_id',
    ];

    public function belongs_to_user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function belongs_to_country()
    {
        return $this->belongsTo(AppCountries::class, 'country_id');
    }

    public function belongs_to_location()
    {
        return $this->belongsTo(AppGeolocations::class, 'location_id');
    }
}
