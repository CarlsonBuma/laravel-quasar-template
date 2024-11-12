<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_avatar', function (Blueprint $table) {
            // Collaboration
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->boolean('is_community')->default(false);
            $table->boolean('is_available')->default(false);
            $table->date('date_of_availability')->nullable();
            // Credentials
            $table->string('contact')->nullable();
            $table->boolean('contact_is_public')->default(false);
            $table->unsignedBigInteger('age')->nullable();
            $table->boolean('age_is_public')->default(false);
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->boolean('location_is_public')->default(false);
            $table->text('about')->nullable();
            $table->timestamps();
            // Correlations
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
        Schema::dropIfExists('user_avatar');
    }
};