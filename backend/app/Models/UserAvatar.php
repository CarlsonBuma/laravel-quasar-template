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
        'is_community',
        'is_available',
        'date_of_availability',
        'contact',
        'contact_is_public',
        'age',
        'age_is_public',
        'country_id',
        'location_id',
        'location_is_public',
        'about',
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
