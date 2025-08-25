<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Address;
use App\Models\User;
use App\Models\Province;
use App\Models\City;
use App\Models\District;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users, provinces, cities, and districts
        $users = User::whereHas('role', function($query) {
            $query->where('name', 'customer');
        })->get();

        $provinces = Province::all();
        $cities = City::all();
        $districts = District::all();

        if ($users->isEmpty() || $provinces->isEmpty() || $cities->isEmpty()) {
            $this->command->warn('Skipping AddressSeeder: No users, provinces, or cities found.');
            return;
        }

        $addressLabels = ['Rumah', 'Kantor', 'Kos', 'Toko', 'Gudang'];
        $addressTypes = ['home', 'office', 'warehouse', 'other'];

        foreach ($users as $user) {
            // Check if user already has addresses
            $existingAddresses = $user->addresses()->count();
            if ($existingAddresses > 0) {
                $this->command->info("User {$user->name} already has {$existingAddresses} addresses, skipping...");
                continue;
            }

            // Create 2-4 addresses per user
            $numAddresses = rand(2, 4);

            for ($i = 0; $i < $numAddresses; $i++) {
                $province = $provinces->random();
                $city = $cities->where('province_id', $province->id)->first();

                if (!$city) {
                    $city = $cities->random();
                }

                $district = $districts->where('city_id', $city->id)->first();

                $label = $addressLabels[array_rand($addressLabels)];
                $type = $addressTypes[array_rand($addressTypes)];

                // Generate realistic address
                $streetNumber = rand(1, 999);
                $streetName = $this->getRandomStreetName();
                $rt = rand(1, 20);
                $rw = rand(1, 20);
                $postalCode = rand(10000, 99999);

                // Generate address line
                $addressLine = "Jl. {$streetName} No. {$streetNumber}, RT {$rt}/RW {$rw}";
                if ($district) {
                    $addressLine .= ", {$district->name}";
                }
                $addressLine .= ", {$city->name}, {$province->name}";

                Address::create([
                    'user_id' => $user->id,
                    'label' => $label,
                    'type' => $type,
                    'recipient_name' => $user->name,
                    'phone' => $user->phone ?? '08123456789',
                    'province_id' => $province->id,
                    'city_id' => $city->id,
                    'district_id' => $district ? $district->id : null,
                    'postal_code' => $postalCode,
                    'address_line' => "Jl. {$streetName} No. {$streetNumber}, RT {$rt}/RW {$rw}",
                    'is_primary' => $i === 0, // First address is primary
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('AddressSeeder completed successfully!');
    }

    /**
     * Get random street names
     */
    private function getRandomStreetName(): string
    {
        $streetNames = [
            'Sudirman', 'Thamrin', 'Gatot Subroto', 'M.H. Thamrin', 'Jendral Sudirman',
            'Kemayoran', 'Mangga Dua', 'Hayam Wuruk', 'Gajah Mada', 'Malioboro',
            'Solo', 'Semarang', 'Yogyakarta', 'Surabaya', 'Bandung',
            'Merdeka', 'Independence', 'Freedom', 'Liberty', 'Democracy',
            'Pancasila', 'Reformasi', 'Kemerdekaan', 'Persatuan', 'Kesatuan',
            'Cendrawasih', 'Rajawali', 'Garuda', 'Elang', 'Merpati',
            'Mawar', 'Melati', 'Anggrek', 'Tulip', 'Lily',
            'Jaya', 'Makmur', 'Sejahtera', 'Bahagia', 'Sukses'
        ];

        return $streetNames[array_rand($streetNames)];
    }
}
