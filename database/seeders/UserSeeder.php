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

        // Create Customers
        $customers = [
            [
                'name' => 'member',
                'email' => 'member@afiyah.com',
                'phone' => '081234567896',
                'address' => 'Jl. Customer No. 1, Makassar',
            ],
            [
                'name' => 'Gita Sari',
                'email' => 'gita@example.com',
                'phone' => '081234567897',
                'address' => 'Jl. Customer No. 2, Makassar',
            ],
            [
                'name' => 'Hendra Wijaya',
                'email' => 'hendra@example.com',
                'phone' => '081234567898',
                'address' => 'Jl. Customer No. 3, Makassar',
            ],
            [
                'name' => 'Indah Permata',
                'email' => 'indah@example.com',
                'phone' => '081234567899',
                'address' => 'Jl. Customer No. 4, Makassar',
            ],
            [
                'name' => 'Joko Widodo',
                'email' => 'joko@example.com',
                'phone' => '081234567800',
                'address' => 'Jl. Customer No. 5, Makassar',
            ],
            [
                'name' => 'Kartika Sari',
                'email' => 'kartika@example.com',
                'phone' => '081234567801',
                'address' => 'Jl. Customer No. 6, Makassar',
            ],
            [
                'name' => 'Lukman Hakim',
                'email' => 'lukman@example.com',
                'phone' => '081234567802',
                'address' => 'Jl. Customer No. 7, Makassar',
            ],
            [
                'name' => 'Maya Indah',
                'email' => 'maya@example.com',
                'phone' => '081234567803',
                'address' => 'Jl. Customer No. 8, Makassar',
            ],
            [
                'name' => 'Nugraha Pratama',
                'email' => 'nugraha@example.com',
                'phone' => '081234567804',
                'address' => 'Jl. Customer No. 9, Makassar',
            ],
            [
                'name' => 'Oktavia Putri',
                'email' => 'oktavia@example.com',
                'phone' => '081234567805',
                'address' => 'Jl. Customer No. 10, Makassar',
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
