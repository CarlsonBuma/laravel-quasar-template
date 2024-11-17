<?php

namespace App\Models;

use App\Models\Users;
use App\Models\AccessUsers;
use App\Models\AppCountries;
use App\Models\AppGeolocations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Entities extends Model
{
    use HasFactory;

    protected $table = 'public.entities';

    protected $fillable = [
        'user_id',          // Owner
        'is_public',        // Flag
        'name',
        'avatar',
        'about',
        'contact',
        'website',
        'location_id',
        'country_id',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function belongs_to_user() {
        return $this->belongsTo(Users::class, 'user_id');
    }

    public function belongs_to_location() {
        return $this->belongsTo(AppGeolocations::class, 'location_id');
    }

    public function belongs_to_country() {
        return $this->belongsTo(AppCountries::class, 'country_id');
    }

    public function has_user_access_pivot() {
        return $this->hasMany(AccessUsers::class, 'entity_id');
    }
}
