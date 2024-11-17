<?php

namespace App\Models;

use App\Models\Admins;
use App\Models\Entities;
use App\Models\AccessTransactions;
use Laravel\Passport\HasApiTokens;
use App\Models\AccessSubscriptions;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Users extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'public.users';

    protected $fillable = [
        'name',
        'avatar',
        'email',
        'password',
        'token',
        'email_verified_at',    // Flag
        'is_public',            // Flag
        'archived'              // Flag
    ];

    protected $hidden = [
        'password',
        'token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'archived' => 'datetime',
    ];

    public function has_entity() {
        return $this->hasOne(Entities::class, 'user_id');
    }

    //* Access
    public function is_admin() {
        return $this->hasOne(Admins::class, 'user_id');
    }

    //* Payments
    public function has_subsciptions() {
        return $this->hasMany(AccessSubscriptions::class, 'user_id');
    }

    public function has_transactions() {
        return $this->hasMany(AccessTransactions::class, 'user_id');
    }

    public function has_user_access_pivot() {
        return $this->hasMany(AccessUsers::class, 'user_id');
    }
}
