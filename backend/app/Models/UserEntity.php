<?php

namespace App\Models;

use App\Models\User;
use App\Models\AccessUsers;
use App\Models\AppCountries;
use App\Models\AppGeolocations;
use App\Models\UserEntityVectors;
use App\Models\PivotUserEntityShortcuts;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserEntity extends Model
{
    use HasFactory;

    protected $table = 'public.user_entity';

    protected $fillable = [
        'user_id',
        'is_community',
        'name',
        'slogan',
        'avatar',
        'about',
        'contact',
        'website',
        'tags',
        'country_id',
        'location_id'
    ];

    protected $casts = [
        'foundation' => 'date:Y-m-d',
        'tags' => 'array',
    ];

    public function belongs_to_user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function belongs_to_location() {
        return $this->belongsTo(AppGeolocations::class, 'location_id');
    }

    public function belongs_to_country() {
        return $this->belongsTo(AppCountries::class, 'country_id');
    }

    public function has_followers() {
        return $this->hasMany(PivotUserEntityShortcuts::class, 'entity_id');
    }

    public function has_collaborations() {
        return $this->hasMany(UserEntityCollaborations::class, 'entity_id');
    }

    public function has_collaborators() {
        return $this->hasMany(Collaborators::class, 'entity_id');
    }

    public function has_subsciption_access_pivot() {
        return $this->hasMany(AccessUsers::class, 'entity_id');
    }
}
