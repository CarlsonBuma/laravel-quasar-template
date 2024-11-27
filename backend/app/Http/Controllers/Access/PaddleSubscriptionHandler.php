<?php

namespace App\Http\Controllers\Access;

use App\Models\PaddleSubscriptions;


class PaddleSubscriptionHandler
{
    public $subscription = null;

    /**
     * Set subription
     *
     * @param object|null $subscription
     * @return void
     */
    function __construct(object $subscription = null)
    {
        $this->subscription = $subscription;
    }

    /**
     * Update current Subscription
     * https://developer.paddle.com/webhooks/overview
     *
     * @param array $contentData
     * @return void
     */
    public function updateSubscriptionByWebhook(array $contentData): void
    {
        $this->subscription = PaddleSubscriptions::where([
            'subscription_token' => $contentData['id'],
        ])->first();

        if(!$this->subscription) return;
        $subscriptionStatus = $contentData['status'];
        $startedAt = $contentData['started_at'];
        $canceledAt = $contentData['canceled_at'];
        $pausedAt = $contentData['paused_at'];
        $this->subscription->update([
            'started_at' => $startedAt,
            'canceled_at' => $canceledAt,
            'paused_at' => $pausedAt,
            'status' => $subscriptionStatus,
            'message' => 'webhook.subscription.updated'
        ]);
    }
}
