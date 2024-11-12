<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pivot_user_languages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('language_id');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id')
                ->on('public.users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('language_id')
                ->references('id')
                ->on('public.app_languages')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pivot_user_languages');
    }
};