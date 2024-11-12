<?php

namespace App\Models;

use App\Models\Admin;
use App\Models\UserAvatar;
use App\Models\UserEntity;
use App\Models\PivotUserLanguages;
use App\Models\AccessTransactions;
use App\Models\PivotUserEntityShortcuts;
use Laravel\Passport\HasApiTokens;
use App\Models\AccessSubscriptions;
use Illuminate\Notifications\Notifiable;
use App\Models\Collaborators;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'public.users';

    protected $fillable = [
        'name',
        'email',
        'avatar',
        'password',
        'token'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function has_entity() {
        return $this->hasOne(UserEntity::class, 'user_id');
    }

    public function has_avatar() {
        return $this->hasOne(UserAvatar::class, 'user_id');
    }

    public function has_collaborations() {
        return $this->hasMany(PivotUserEntityShortcuts::class, 'user_id');
    }

    public function has_language_pivot() {
        return $this->hasMany(PivotUserLanguages::class, 'user_id');
    }

    public function has_subsciptions() {
        return $this->hasMany(AccessSubscriptions::class, 'user_id');
    }

    public function has_transactions() {
        return $this->hasMany(AccessTransactions::class, 'user_id');
    }

    public function has_subsciption_access_pivot() {
        return $this->hasMany(AccessUsers::class, 'user_id');
    }

    public function has_collaborators() {
        return $this->hasMany(Collaborators::class, 'user_id');
    }

    public function is_admin() {
        return $this->hasOne(Admin::class, 'user_id');
    }
}
