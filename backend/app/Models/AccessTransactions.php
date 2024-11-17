<?php

namespace App\Models;

use App\Models\Users;
use App\Models\AccessPrices;
use App\Models\AccessSubscriptions;
use App\Models\AccessUsers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccessTransactions extends Model
{
    use HasFactory;

    protected $table = 'public.access_transactions';

    protected $fillable = [
        'user_id',
        'subscription_id',
        'transaction_token',
        'customer_token',
        'price_id',
        'quantity',
        'total',
        'tax',
        'currency_code',
        'access_added',
        'is_verified',
        'status',
        'message',
    ];

    protected $casts = [
        'total' => 'float:2',
        'tax' => 'float:2',
        
    ];

    public function belongs_to_user() {
        return $this->belongsTo(Users::class, 'user_id');
    }

    public function belongs_to_subscription() {
        return $this->belongsTo(AccessSubscriptions::class, 'subscription_id');
    }

    public function belongs_to_price() {
        return $this->belongsTo(AccessPrices::class, 'price_id');
    }

    public function has_user_access_pivot() {
        return $this->hasMany(AccessUsers::class, 'transaction_id');
    }
}
