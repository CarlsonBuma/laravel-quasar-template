<?php

namespace App\Http\Controllers\Auth\AppAccess;

use Carbon\Carbon;
use App\Models\AccessPrices;
use App\Models\AccessTransactions;
use App\Http\Middleware\AppAccess;
use App\Models\AccessSubscriptions;


class PaddleTransactionHandler
{
    // Access
    public $user_id = null;
    public $access = null;
    // Transaction
    public $transaction = null;
    public $transaction_token = null;
    // Subscription
    public $subscription = null;
    public $subscription_token = null;
    // Price
    public $price = null;
    public $price_token = null;
    public $price_id = null;
    // Access
    public $access_token = 'default-token';
    public $access_quantity = 1;
    public $expiration_date = null;
    public $quantity = 1;
    // Meta
    public $status = 'processing...';
    public $customer_token = null;
    public $total = 0.00;
    public $tax = 0.00;
    public $currencyCode = 'CHF';

    /**
     * Set default attributes
     *  $transaction 
     *  $transaction_token
     *  $user_id
     *
     * @param object|null $transactionEntry
     */
    function __construct(object $transactionEntry = null)
    {
        if(!$transactionEntry) return;
        $this->transaction = $transactionEntry;
        $this->transaction_token = $this->transaction->transaction_token;
        $this->user_id = $this->transaction->user_id;
    }

    /**
     * Set Attributes
     *  > https://developer.paddle.com/webhooks/overview
     *  > According API Response
     *
     * @param array $contentData
     * @return void
     */
    public function setTransactionAttributes(array $contentData): void
    {
        // Transaction Attributes
        $item = $contentData['items'][0] ?? null;
        $this->transaction_token = $contentData['id'];
        $this->price_token = $item['price']['id'] ?? null;
        $this->customer_token = $contentData['customer_id'];
        $this->quantity = (int) $item['quantity'] ?? 0;
        $this->total = ((float) $contentData['details']['totals']['total']) / 100 ?? 0;
        $this->tax = ((float) $contentData['details']['totals']['tax']) / 100 ?? 0;
        $this->currencyCode = $contentData['currency_code'] ?? 'CHF';
        $this->status = $contentData['status'];

        // Access Attributes
        $this->price = AccessPrices::where('price_token', $this->price_token)->first();
        $this->price_id = $this->price?->id;
        $this->access_token = $this->price->access_token 
            ?? $item['price']['custom_data']['access_token']
                ?? $this->access_token;
        $defaultPeriod = $item['price']['custom_data']['duration_months'] ?? 0;
        $this->expiration_date = $contentData['current_billing_period']['ends_at'] 
            ?? $contentData['billing_period']['ends_at']
                ?? $this->calculateLatestUserExpirationDate($defaultPeriod);

        // Subscription
        $this->subscription_token = $contentData['subscription_id'] ?? null;
    }

    /**
     * Initialize Transaction via Subscription Webhook
     *
     * @return void
     */
    public function initializeClientTransactionByUserSubscription(): void
    {
        $this->subscription = AccessSubscriptions::where('subscription_token', $this->subscription_token)->first();
        if(!$this->subscription) return;
        $this->user_id = $this->subscription->user_id;
        $this->initializeClientTransaction($this->user_id, $this->transaction_token);
    }

    /**
     * Create User Transaction via Client
     *
     * @param integer $userID
     * @param string $transactionToken
     * @return void
     */
    public function initializeClientTransaction(int $userID, string $transactionToken): void
    {
        $this->transaction = AccessTransactions::firstOrCreate([
            'user_id' => $userID,
            'transaction_token' => $transactionToken
        ], [
            'status' => 'initialized',
            'message' => 'transaction.initialized',
        ]);
    }

    /**
     * Check if Transaction belongs to Subscription
     * There might be already a subscription existing
     *
     * @return void
     */
    public function setSubscription(): void
    {
        if(!$this->subscription_token || !$this->user_id) return;
        $this->subscription = AccessSubscriptions::firstOrCreate([
            'user_id' => $this->user_id,
            'subscription_token' => $this->subscription_token,
        ], [
            'price_id' => $this->price_id,
            'started_at' => now(),
            'status' => 'active',
            'message' => 'subscription.verified'
        ]);
    }

    /**
     * Complete Transaction
     *
     * @return void
     */
    public function completeTransaction(): void
    {
        if(!$this->transaction) return;
        $this->transaction->update([
            'subscription_id' => $this->subscription?->id,
            'customer_token' => $this->customer_token,
            'price_id' => $this->price_id,
            'quantity' => $this->quantity,
            'total' => $this->total,
            'tax' => $this->tax,
            'currency_code' => $this->currencyCode,
            'is_verified' => true,
            'status' => $this->status,
            'message' => 'transaction.completed'
        ]);
    }

    /**
     ** User-access: Calculate expiration date
     *  > We get expiration_date by Provider
     *  > We need to extend current access
     *
     * @param integer $accessPeriod
     * @return string
     */
    private function calculateLatestUserExpirationDate(int $accessPeriod): string
    {
        $expirationDate = Carbon::now()->addMonths($accessPeriod * $this->quantity);
        $currentAccess = AppAccess::checkUserAccessByToken($this->transaction->user_id, $this->access_token);

        // Current access is active
        // Add month to current expiration Date
        if($currentAccess) {
            $expirationDate = Carbon::parse($currentAccess->expiration_date);
            $expirationDate = $expirationDate->addMonths($accessPeriod * $this->quantity);
        }

        return $expirationDate;
    }
}
