<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->string('transaction_token', 255)->unique();
            $table->string('customer_token', 255)->nullable();

            // Billing details
            $table->unsignedBigInteger('price_id')->nullable();
            $table->unsignedBigInteger('quantity')->default(1);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->decimal('tax', 10, 2)->default(0.00);
            $table->string('currency_code', 3)->default('CHF');          
            
            // Payment Meta
            $table->boolean('access_added')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->string('status', 99);
            $table->text('message', 255)->nullable();
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id')
                ->on('public.users')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->foreign('subscription_id')
                ->references('id')
                ->on('public.access_subscriptions')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->foreign('price_id')
                ->references('id')
                ->on('public.access_prices')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index(['transaction_token', 'customer_token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_transactions');
    }
};
