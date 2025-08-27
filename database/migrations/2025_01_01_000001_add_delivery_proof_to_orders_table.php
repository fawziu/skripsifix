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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('pickup_proof_photo')->nullable()->after('metadata');
            $table->string('delivery_proof_photo')->nullable()->after('pickup_proof_photo');
            $table->timestamp('pickup_proof_at')->nullable()->after('delivery_proof_photo');
            $table->timestamp('delivery_proof_at')->nullable()->after('pickup_proof_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'pickup_proof_photo',
                'delivery_proof_photo',
                'pickup_proof_at',
                'delivery_proof_at'
            ]);
        });
    }
};
