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

            // Create primary address first
            try {
                $province = $provinces->random();
                $city = $cities->where('province_id', $province->id)->first() ?? $cities->random();
                $district = $districts->where('city_id', $city->id)->first();
                
                $coordinates = $this->getRealisticCoordinates();
                $streetNumber = rand(1, 999);
                $streetName = $this->getRandomStreetName();
                $rt = rand(1, 20);
                $rw = rand(1, 20);
                $postalCode = rand(10000, 99999);

                // Create primary address
                Address::create([
                    'user_id' => $user->id,
                    'label' => 'Alamat Utama',
                    'type' => 'home',
                    'recipient_name' => $user->name,
                    'phone' => $user->phone ?? '08123456789',
                    'province_id' => $province->id,
                    'city_id' => $city->id,
                    'district_id' => $district ? $district->id : null,
                    'postal_code' => $postalCode,
                    'address_line' => "Jl. {$streetName} No. {$streetNumber}, RT {$rt}/RW {$rw}",
                    'latitude' => $coordinates['latitude'],
                    'longitude' => $coordinates['longitude'],
                    'accuracy' => $coordinates['accuracy'],
                    'is_primary' => true,
                    'is_active' => true,
                ]);

                // Create 1-3 additional non-primary addresses
                $additionalAddresses = rand(1, 3);
                
                for ($i = 0; $i < $additionalAddresses; $i++) {
                    $province = $provinces->random();
                    $city = $cities->where('province_id', $province->id)->first() ?? $cities->random();
                    $district = $districts->where('city_id', $city->id)->first();
                    
                    $label = $addressLabels[array_rand($addressLabels)];
                    $type = $addressTypes[array_rand($addressTypes)];
                    
                    $coordinates = $this->getRealisticCoordinates();
                    $streetNumber = rand(1, 999);
                    $streetName = $this->getRandomStreetName();
                    $rt = rand(1, 20);
                    $rw = rand(1, 20);
                    $postalCode = rand(10000, 99999);

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
                        'latitude' => $coordinates['latitude'],
                        'longitude' => $coordinates['longitude'],
                        'accuracy' => $coordinates['accuracy'],
                        'is_primary' => false,
                        'is_active' => true,
                    ]);
                }
            } catch (\Exception $e) {
                $this->command->error("Error creating addresses for user {$user->id}: " . $e->getMessage());
                continue;
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

    /**
     * Generate realistic coordinates for Indonesia
     * Coordinates are centered around major cities with some random variation
     */
    private function getRealisticCoordinates(): array
    {
        $cities = [
            // Kota-kota di Papua
            ['name' => 'Jayapura', 'lat' => -2.533333, 'lng' => 140.716667],
            ['name' => 'Merauke', 'lat' => -8.496667, 'lng' => 140.374444],
            ['name' => 'Timika', 'lat' => -4.549722, 'lng' => 136.885833],
            ['name' => 'Sorong', 'lat' => -0.875000, 'lng' => 131.250000],
            ['name' => 'Manokwari', 'lat' => -0.861111, 'lng' => 134.061944],
            ['name' => 'Nabire', 'lat' => -3.366667, 'lng' => 135.483333],
            ['name' => 'Biak', 'lat' => -1.183333, 'lng' => 136.050000],
            
            // Kota Makassar dan sekitarnya
            ['name' => 'Makassar Utara', 'lat' => -5.119444, 'lng' => 119.412778],
            ['name' => 'Makassar Selatan', 'lat' => -5.153889, 'lng' => 119.436389],
            ['name' => 'Panakkukang', 'lat' => -5.135399, 'lng' => 119.423790],
            ['name' => 'Tamalanrea', 'lat' => -5.129722, 'lng' => 119.488333],
            ['name' => 'Mariso', 'lat' => -5.156944, 'lng' => 119.409722],
        ];

        // Pilih kota secara random
        $city = $cities[array_rand($cities)];
        
        // Tambahkan variasi random (dalam range ±2km)
        $latVariation = (rand(-20000, 20000) / 10000) / 111.32; // 1 derajat = 111.32 km
        $lngVariation = (rand(-20000, 20000) / 10000) / (111.32 * cos(deg2rad($city['lat'])));
        
        return [
            'latitude' => $city['lat'] + $latVariation,
            'longitude' => $city['lng'] + $lngVariation,
            'accuracy' => rand(5, 50), // Akurasi antara 5-50 meter
        ];
    }
}
