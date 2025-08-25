<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourierBankInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all courier IDs
        $courierIds = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('roles.name', 'courier')
            ->pluck('users.id');

        foreach ($courierIds as $courierId) {
            // Check if courier pricing exists
            $pricingExists = DB::table('courier_pricing')
                ->where('courier_id', $courierId)
                ->exists();

            if ($pricingExists) {
                // Update existing courier pricing with bank info
                DB::table('courier_pricing')
                    ->where('courier_id', $courierId)
                    ->update([
                        'bank_info' => json_encode([
                            'bank_name' => 'Bank Central Asia (BCA)',
                            'account_number' => '1234567890',
                            'account_holder' => 'Kurir ' . $courierId,
                            'is_active' => true
                        ])
                    ]);
            } else {
                // Create new courier pricing with bank info
                DB::table('courier_pricing')->insert([
                    'courier_id' => $courierId,
                    'base_fee' => 10000,
                    'per_kg_fee' => 5000,
                    'is_active' => true,
                    'bank_info' => json_encode([
                        'bank_name' => 'Bank Central Asia (BCA)',
                        'account_number' => '1234567890',
                        'account_holder' => 'Kurir ' . $courierId,
                        'is_active' => true
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
