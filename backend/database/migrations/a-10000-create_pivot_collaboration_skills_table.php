<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pivot_collaboration_skills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('collaboration_id');
            $table->unsignedBigInteger('skill_id');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->foreign('collaboration_id')
                ->references('id')
                ->on('public.user_entity_collaborations')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('skill_id')
                ->references('id')
                ->on('public.app_skills')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pivot_collaboration_skills');
    }
};