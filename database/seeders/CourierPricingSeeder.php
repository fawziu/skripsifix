<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CourierPricing;
use App\Models\Role;

class CourierPricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get courier role
        $courierRole = Role::where('name', 'courier')->first();

        if (!$courierRole) {
            $this->command->error('Courier role not found. Please run RoleSeeder first.');
            return;
        }

        // Get all couriers
        $couriers = User::where('role_id', $courierRole->id)
                       ->where('is_active', true)
                       ->get();

        if ($couriers->isEmpty()) {
            $this->command->error('No active couriers found. Please run UserSeeder first.');
            return;
        }

        foreach ($couriers as $courier) {
            // Check if pricing already exists
            $existingPricing = CourierPricing::where('courier_id', $courier->id)->first();

            if (!$existingPricing) {
                // Create pricing with random values
                $baseFee = rand(10000, 25000); // 10k - 25k base fee
                $perKgFee = rand(2000, 5000); // 2k - 5k per kg

                CourierPricing::create([
                    'courier_id' => $courier->id,
                    'base_fee' => $baseFee,
                    'per_kg_fee' => $perKgFee,
                    'is_active' => true,
                ]);

                $this->command->info("Created pricing for courier: {$courier->name} - Base: Rp{$baseFee}, Per Kg: Rp{$perKgFee}");
            } else {
                $this->command->info("Pricing already exists for courier: {$courier->name}");
            }
        }

        $this->command->info('Courier pricing seeding completed!');
    }
}
