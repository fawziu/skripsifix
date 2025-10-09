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
            $table->decimal('origin_latitude', 10, 8)->nullable()->after('origin_address');
            $table->decimal('origin_longitude', 11, 8)->nullable()->after('origin_latitude');
            $table->decimal('destination_latitude', 10, 8)->nullable()->after('destination_address');
            $table->decimal('destination_longitude', 11, 8)->nullable()->after('destination_latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['origin_latitude', 'origin_longitude', 'destination_latitude', 'destination_longitude']);
        });
    }
};
