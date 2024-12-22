<?php

namespace App\Http\Controllers\Access\AccessHandling;

use App\Models\PaddlePrices;
use App\Http\Controllers\Access\AccessHandler;

class PaddlePriceHandler
{
    public $price = null;

    /**
     * Update price changes that have been triggered within Paddle
     * Sandbox: https://sandbox-vendors.paddle.com/products-v2
     * 
     ** Setup: Price Catalog Cockpit
     *  - Set Price and its attributes
     *  - Ensure 'custom_data' is included in the price configuration
     *      > 'access_token' (required): Defines access to the app and its features
     *      > 'duration_months': Defines the period of access
     *          - Note: This is overridden by the subscription.billing_period.ends_at value
     *  - Enable price access token within function "updatePriceByWebhook()"
     *
     * @param array $contentData
     * @return void
     */
    public function updatePriceByWebhook(array $contentData): void
    {
        $accessTokenByPaddle = $contentData['custom_data']['access_token'] ?? null;
        if($accessTokenByPaddle && (
            AccessHandler::$tokenCockpit === $accessTokenByPaddle
            
            // ------------------------------------
            // Allow other price tokens within app here
            // ------------------------------------

        )) { 
            // Price is available and defined within app
            PaddlePrices::updateOrCreate([
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
                'access_token' => $contentData['custom_data']['access_token'] ?? 'undefined',
                'duration_months' => $contentData['custom_data']['duration_months'] ?? 0,
                'status' => $contentData['status'],
                'message' => 'webhook.price.updated',
            ]);
        }
    }
}
