<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_departements', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_public')->default(true);
            $table->string('label');
            $table->text('description')->nullable();
            $table->text('responsibilities')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_departements');
    }
};
