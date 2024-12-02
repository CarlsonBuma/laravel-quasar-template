<?php

namespace App\Http\Controllers\Access;

use App\Http\Middleware\AppAccessCockpit;
use App\Models\PaddlePrices;


class PaddlePriceHandler
{
    // App Price Model
    public $price = null;

    /**
     * Update or create price via our webhook listener.
     * See File: "/Listeners/PaddleWebhookListener".
     * 
     * Prices are configured within the Paddle Cockpit. 
     * Ensure that the price tokens are correctly defined:
     *  > Setup price and its data in Paddle Cockpit
     *      > 'access_token' (required): Defines app / features access
     *      > 'duration_months': Defines period of current access
     *  > Set access token within .env-file
     *  > Setup logic witin app
     *  > Allow new access token within app, within this file
     * 
     * @param array $contentData
     * @return void
     */
    public function updatePriceByWebhook(array $contentData): void
    {
        // Restrict prices to token set within system - see Middleware \Access{Name}
        $accessTokenByPaddle = $contentData['custom_data']['access_token'] ?? null;
        if($accessTokenByPaddle && (
            AccessHandler::$tokenCockpit === $accessTokenByPaddle
            
            // ------------------------------------
            // Allow other tokens within app here
            // ------------------------------------

        )) { 
            // Price is available and defined within app
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
                'access_token' => $contentData['custom_data']['access_token'] ?? 'undefined',
                'duration_months' => $contentData['custom_data']['duration_months'] ?? 0,
                'status' => $contentData['status'],
                'message' => 'webhook.price.updated',
            ]);
        }
    }
}
