<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_entity', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->boolean('is_community')->default(false);
            $table->string('avatar')->nullable();
            $table->string('name')->nullable();
            $table->string('slogan')->nullable();
            $table->text('about')->nullable();
            $table->text('contact')->nullable();
            $table->string('website')->nullable();
            $table->text('tags')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id')
                ->on('public.users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('country_id')
                ->references('id')
                ->on('public.app_countries')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->foreign('location_id')
                ->references('id')
                ->on('public.app_geolocations')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_entity');
    }
};