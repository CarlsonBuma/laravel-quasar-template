<?php

namespace App\Models;

use App\Models\UserEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * DB seeder
 */
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

    public function has_entities() {
        return $this->hasMany(UserEntity::class, 'country_id');
    }
}
