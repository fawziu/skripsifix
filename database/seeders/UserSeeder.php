<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('name', 'admin')->first();
        $courierRole = Role::where('name', 'courier')->first();
        $customerRole = Role::where('name', 'customer')->first();

        // Create Admin
        User::updateOrCreate(
            ['email' => 'admin@Afiyah.com'],
            [
                'name' => 'System Administrator',
                'email' => 'admin@Afiyah.com',
                'password' => Hash::make('password'),
                'phone' => '081234567890',
                'address' => 'Jl. Admin No. 1, Jakarta',
                'role_id' => $adminRole->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create Couriers
        $couriers = [
            [
                'name' => 'Ahmad Supriadi',
                'email' => 'ahmad@Afiyah.com',
                'phone' => '081234567891',
                'address' => 'Jl. Kurir No. 1, Makassar',
            ],
            [
                'name' => 'kurir',
                'email' => 'kurir@afiyah.com',
                'phone' => '081234567892',
                'address' => 'Jl. Kurir No. 2, Makassar',
            ],
            [
                'name' => 'Citra Dewi',
                'email' => 'citra@Afiyah.com',
                'phone' => '081234567893',
                'address' => 'Jl. Kurir No. 3, Makassar',
            ],
            [
                'name' => 'Dedi Kurniawan',
                'email' => 'dedi@Afiyah.com',
                'phone' => '081234567894',
                'address' => 'Jl. Kurir No. 4, Makassar',
            ],
            [
                'name' => 'Eka Putri',
                'email' => 'eka@Afiyah.com',
                'phone' => '081234567895',
                'address' => 'Jl. Kurir No. 5, Makassar',
            ],
        ];

        foreach ($couriers as $courier) {
            User::updateOrCreate(
                ['email' => $courier['email']],
                [
                    'name' => $courier['name'],
                    'email' => $courier['email'],
                    'password' => Hash::make('password'),
                    'phone' => $courier['phone'],
                    'address' => $courier['address'],
                    'role_id' => $courierRole->id,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        // Create Customers (only 3 customers with specific phone numbers)
        $customers = [
            [
                'name' => 'Customer 1',
                'email' => 'customer1@afiyah.com',
                'phone' => '087866707600',
                'address' => 'Jl. Customer No. 1, Makassar',
            ],
            [
                'name' => 'Customer 2',
                'email' => 'customer2@afiyah.com',
                'phone' => '081240935073',
                'address' => 'Jl. Customer No. 2, Makassar',
            ],
            [
                'name' => 'Customer 3',
                'email' => 'customer3@afiyah.com',
                'phone' => '082195497261',
                'address' => 'Jl. Customer No. 3, Makassar',
            ],
        ];

        foreach ($customers as $customer) {
            User::updateOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'email' => $customer['email'],
                    'password' => Hash::make('password'),
                    'phone' => $customer['phone'],
                    'address' => $customer['address'],
                    'role_id' => $customerRole->id,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
