<?php

namespace App\Models;

use App\Models\UserAvatar;
use App\Models\UserEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppCountries extends Model
{
    use HasFactory;

    protected $table = 'public.app_countries';

    protected $fillable = [
        'is_public',
        'name',
        'dial_code',
        'code'
    ];

    public function has_user_avatars() {
        return $this->hasMany(UserAvatar::class, 'country_id');
    }

    public function has_user_entities() {
        return $this->hasMany(UserEntity::class, 'country_id');
    }
}
