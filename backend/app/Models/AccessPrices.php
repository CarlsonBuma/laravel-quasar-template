<?php

namespace App\Models;


use App\Models\AccessTransactions;
use App\Models\AccessSubscriptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccessPrices extends Model
{
    use HasFactory;

    protected $table = 'public.access_prices';

    protected $fillable = [
        'price_token',
        'product_token',
        'name',
        'description',
        'type',
        'price',
        'tax_mode',
        'currency_code',
        'billing_interval',
        'billing_frequency',
        'trial_interval',
        'trial_frequency',
        'access_token',
        'duration_months',
        'is_active',
        'status',
        'message',
    ];

    public function has_subscriptions() {
        return $this->hasMany(AccessSubscriptions::class, 'price_id');
    }

    public function has_transactions() {
        return $this->hasMany(AccessTransactions::class, 'price_id');
    }
}
