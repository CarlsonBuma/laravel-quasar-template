<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_awards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->boolean('is_public')->default(false);
            $table->string('access_token')->nullable();
            $table->string('label');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('credits')->default(1);
            $table->unsignedBigInteger('evaluation')->default(0);
            $table->string('icon')->nullable();
            $table->date('archived')->nullable();
            $table->timestamps();
            $table->foreign('entity_id')
                ->references('id')
                ->on('public.user_entity')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_awards');
    }
};
