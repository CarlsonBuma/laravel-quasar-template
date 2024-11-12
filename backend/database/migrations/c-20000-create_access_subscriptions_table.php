<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('price_id')->nullable();
            $table->string('subscription_token', 255)->unique();
            $table->date('started_at')->nullable();
            $table->date('canceled_at')->nullable();
            $table->date('paused_at')->nullable();
            
            // Payment Meta
            $table->string('status', 99);
            $table->text('message', 255)->nullable();
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id')
                ->on('public.users')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->foreign('price_id')
                ->references('id')
                ->on('public.access_prices')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index(['subscription_token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_subscriptions');
    }
};
