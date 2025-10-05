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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('brand');
            $table->string('slug')->unique();
            $table->string('model');
            $table->string('license_plate')->unique();
            $table->enum('transmission', ['manual', 'automatic']);
            $table->integer('year');
            $table->decimal('daily_rate', 10, 2);
            $table->integer('passenger_capacity');
            $table->enum('fuel_type', ['gasoline', 'diesel', 'electric']);
            $table->enum('status', ['available', 'rented', 'maintenance'])->default('available');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('penalty_rate_per_day', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
