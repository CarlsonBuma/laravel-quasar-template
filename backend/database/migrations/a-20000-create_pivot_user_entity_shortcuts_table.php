<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


// A user (User_ID) is connected
// To a entity (Entity_ID)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pivot_user_entity_shortcuts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('entity_id');          
            $table->text('notes')->nullable();
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
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pivot_user_entity_shortcuts');
    }
};