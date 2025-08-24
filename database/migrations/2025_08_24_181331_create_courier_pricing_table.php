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
        Schema::create('courier_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courier_id')->constrained('users')->onDelete('cascade');
            $table->decimal('base_fee', 10, 2)->default(0); // Base fee for delivery
            $table->decimal('per_kg_fee', 10, 2)->default(0); // Additional fee per kg
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Ensure one pricing record per courier
            $table->unique('courier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_pricing');
    }
};
