<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('description');
            $table->string('image')->nullable();
            $table->integer('category_id');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('cooking_time');
            $table->integer('difficulty');
            $table->decimal('rating')->nullable();
            $table->integer('rating_count');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
