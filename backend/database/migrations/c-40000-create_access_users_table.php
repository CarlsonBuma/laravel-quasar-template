<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('access_token')->nullable();
            $table->unsignedBigInteger('quantity')->default(1);
            $table->date('expiration_date')->nullable();
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id')
                ->on('public.users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('entity_id')
                ->references('id')
                ->on('public.user_entity')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->foreign('transaction_id')
                ->references('id')
                ->on('public.access_transactions')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_users');
    }
};
