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
        Schema::table('courier_pricing', function (Blueprint $table) {
            $table->json('bank_info')->nullable()->after('per_kg_fee'); // Bank details for transfer payment
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courier_pricing', function (Blueprint $table) {
            $table->dropColumn('bank_info');
        });
    }
};
