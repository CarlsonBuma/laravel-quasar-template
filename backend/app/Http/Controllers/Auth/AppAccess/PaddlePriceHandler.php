<?php

namespace App\Http\Controllers\Auth\AppAccess;

use App\Models\PaddlePrices;


class PaddlePriceHandler
{
    public $price = null;

    /**
     * Set Price
     *
     * @param object|null $price
     * @return void
     */
    function __construct(object $price = null) 
    {
        $this->price = $price;
    }

    /**
     * Update / Create Price
     *
     * @param array $contentData
     * @return void
     */
    public function updatePriceByWebhook(array $contentData): void
    {
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

        // Set price manually to active
        // Webhook 'price.updated' is sending multiple server requests,
        // after price has been 'archived' / 'unarchived' via Paddle Cockpit
        // sometimes $status === archived, sometimes not... -BUG?
        // if($contentData['status'] === 'archived') {
        //     $this->price->is_active = false;
        //     $this->price->save();
        // }
    }
}
