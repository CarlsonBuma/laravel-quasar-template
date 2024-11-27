<?php

namespace App\Http\Controllers\Access;

use App\Models\PaddlePrices;


class PaddlePriceHandler
{
    public $price = null;

    /**
     * Update / create price by webhook (see "/Listeners/PaddleWebhookListener")
     * https://developer.paddle.com/webhooks/overview
     * 
     **Note: Prices are set within Paddle Cockpit
     * Make sure, you define price and 'custom_data' accordingly
     *  > 'access_token' (required): Defines app / features access
     *  > 'duration_months': Defines period of current access
     *      > overwritten, by subscription.billing_period.ends_at
     * 
     * @param array $contentData
     * @return void
     */
    public function updatePriceByWebhook(array $contentData): void
    {
        // Restrict prices to assigned PADDLE_PRODUCT (see .env) - if needed
        // If no product_key is set, all prices within Paddle will be stored
        if( 
            env('PADDLE_PRODUCT_KEY') 
            && env('PADDLE_PRODUCT_KEY') !== $contentData['product_id']
        ) return;

        // Set Price via webhook
        $this->price = PaddlePrices::updateOrCreate([
            'price_token' => $contentData['id'],
        ], [
            'product_token' => $contentData['product_id'],
            'name' => $contentData['name'],
            'description' => $contentData['description'],
            'type' => $contentData['billing_cycle'] ? 'subscription' : 'one-time-purchase',
            'price' => $contentData['unit_price']['amount'] / 100 ?? null,
            'tax_mode' => $contentData['tax_mode'] === 'external' ? 'excluded' : 'included',
            'currency_code' => $contentData['unit_price']['currency_code'] ?? null,
            'billing_interval' => $contentData['billing_cycle']['interval'] ?? null,
            'billing_frequency' => $contentData['billing_cycle']['frequency'] ?? null,
            'trial_interval' => $contentData['trial_period']['interval'] ?? null,
            'trial_frequency' => $contentData['trial_period']['frequency'] ?? null,
            'access_token' => $contentData['custom_data']['access_token'] ?? 'default-access',
            'duration_months' => $contentData['custom_data']['duration_months'] ?? 0,
            'status' => $contentData['status'],
            'message' => 'webhook.price.updated',
        ]);
    }
}
