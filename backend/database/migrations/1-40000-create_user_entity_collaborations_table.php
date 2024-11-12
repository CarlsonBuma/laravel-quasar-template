<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_entity_collaborations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->unsignedBigInteger('award_id')->nullable();
            $table->unsignedBigInteger('departement_id')->nullable();
            $table->string('title');
            $table->string('meta')->nullable();
            $table->string('duration')->nullable();
            $table->text('about');
            $table->text('details')->nullable();
            
            $table->string('token')->nullable();
            $table->unsignedBigInteger('access_limit')->default(1);
            $table->boolean('is_public')->default(false);
            $table->text('tags')->nullable();

            $table->date('archived')->nullable();
            $table->timestamps();
            $table->foreign('entity_id')
                ->references('id')
                ->on('public.user_entity')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->foreign('departement_id')
                ->references('id')
                ->on('public.app_departements')
                ->onUpdate('cascade')
                ->onDelete('set null');
            $table->foreign('award_id')
                ->references('id')
                ->on('public.app_awards')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_entity_collaborations');
    }
};