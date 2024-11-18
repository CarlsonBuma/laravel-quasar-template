<?php

namespace App\Models;


use App\Models\PaddleTransactions;
use App\Models\PaddleSubscriptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Seed by Paddle Webhook
 */
class PaddlePrices extends Model
{
    use HasFactory;

    protected $table = 'public.paddle_prices';

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
        return $this->hasMany(PaddleSubscriptions::class, 'price_id');
    }

    public function has_transactions() {
        return $this->hasMany(PaddleTransactions::class, 'price_id');
    }
}
