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
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('car_id')->constrained('cars')->onDelete('cascade');
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->onDelete('cascade');

            $table->date('rental_start_date');
            $table->date('rental_end_date');
            $table->integer('total_days');

            $table->enum('usage_type', ['in_city', 'out_of_city']);

            $table->decimal('base_rental_fee', 10, 2);
            $table->decimal('additional_fee', 10, 2)->default(0);
            $table->decimal('total_fee', 10, 2);

            $table->date('return_date')->nullable();
            $table->decimal('late_fee', 10, 2)->default(0);

            $table->string('late_fee_payment_proof')->nullable();

            $table->boolean('is_returned')->default(false);
            $table->enum('status', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending');

            $table->string('payment_type')->default('manual');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
