<?php

namespace App\Models;

use App\Models\UserEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppGeolocations extends Model
{
    use HasFactory;

    protected $table = 'public.app_geolocations';

    protected $fillable = [
        'place_id',
        'lng',
        'lat',
        'address',
        'country',
        'country_short',
        'area',
        'area_short',
        'zip_code'
    ];

    public function has_user_entities() {
        return $this->hasMany(UserEntity::class, 'location_id');
    }

    public function has_user_avatars() {
        return $this->hasMany(UserAvatar::class, 'location_id');
    }
}
