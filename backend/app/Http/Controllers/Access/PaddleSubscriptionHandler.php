<?php

namespace App\Http\Controllers\Access\AccessHandling;

use App\Models\PaddleSubscriptions;

class PaddleSubscriptionHandler
{
    public $subscription = null;

    /**
     ** Update current Subscription
     *
     * @param array $contentData
     * @return void
     */
    public function updateSubscriptionByWebhook(array $contentData): void
    {
        $subscription = PaddleSubscriptions::where([
            'subscription_token' => $contentData['id'],
        ])->first();

        if(!$subscription) return;
        $subscription->update([
            'started_at' => $contentData['started_at'],
            'canceled_at' => $contentData['canceled_at'],
            'paused_at' => $contentData['paused_at'],
            'status' => $contentData['status'],
            'message' => 'webhook.subscription.updated'
        ]);
    }
}
