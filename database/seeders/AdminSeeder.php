<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole) {
            User::updateOrCreate(
                ['email' => 'admin@jastip.com'],
                [
                    'name' => 'System Administrator',
                    'email' => 'admin@jastip.com',
                    'password' => Hash::make('admin123'),
                    'phone' => '081234567890',
                    'address' => 'Jl. Admin No. 1, Jakarta',
                    'role_id' => $adminRole->id,
                    'is_active' => true,
                ]
            );
        }
    }
}
