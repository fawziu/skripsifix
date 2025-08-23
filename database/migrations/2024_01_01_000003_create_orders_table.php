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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('courier_id')->nullable()->constrained('users');
            $table->foreignId('admin_id')->nullable()->constrained('users');
            
            // Order details
            $table->text('item_description');
            $table->decimal('item_weight', 8, 2); // in kg
            $table->decimal('item_price', 12, 2);
            $table->decimal('service_fee', 8, 2);
            $table->decimal('shipping_cost', 8, 2);
            $table->decimal('total_amount', 12, 2);
            
            // Shipping details
            $table->enum('shipping_method', ['manual', 'rajaongkir']);
            $table->string('origin_address');
            $table->string('destination_address');
            $table->string('origin_city')->nullable();
            $table->string('destination_city')->nullable();
            $table->string('courier_service')->nullable(); // jne, pos, tiki, etc.
            $table->string('tracking_number')->nullable();
            
            // Status
            $table->enum('status', [
                'pending',
                'confirmed',
                'assigned',
                'picked_up',
                'in_transit',
                'delivered',
                'cancelled'
            ])->default('pending');
            
            // RajaOngkir specific
            $table->json('rajaongkir_response')->nullable();
            $table->timestamp('estimated_delivery')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
