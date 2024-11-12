<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaborators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('collaboration_id');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->date('date_requested')->nullable();     // by user
            $table->date('date_released')->nullable();      // by entity
            $table->date('date_issued')->nullable();        // by entity
            $table->date('date_confirmed')->nullable();     // by user
            $table->date('period_start')->nullable();
            $table->string('period_duration')->nullable();
            $table->boolean('is_public')->default(false);   // by user
            $table->string('token')->nullable();
            $table->date('archived')->nullable();
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id')
                ->on('public.users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // restrict: Prevents the deletion of the parent record if 
            // any child records that reference it
            $table->foreign('collaboration_id')
                ->references('id')
                ->on('public.user_entity_collaborations')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->foreign('entity_id')
                ->references('id')
                ->on('public.user_entity')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaborators');
    }
};