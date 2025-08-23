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
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('updated_by')->constrained('users');
            $table->enum('status', [
                'pending',
                'confirmed',
                'assigned',
                'picked_up',
                'in_transit',
                'delivered',
                'cancelled'
            ]);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // for RajaOngkir tracking data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_statuses');
    }
};
