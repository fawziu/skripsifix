<?php

namespace Database\Seeders;

use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Services\LocalGeographicalService;
use Illuminate\Database\Seeder;

class GeographicalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $geographicalService = new LocalGeographicalService();

        // Get provinces
        $provinces = $geographicalService->getProvinces();

        foreach ($provinces as $provinceData) {
            $province = Province::updateOrCreate(
                ['rajaongkir_id' => $provinceData['rajaongkir_id']],
                [
                    'name' => $provinceData['name'],
                    'is_active' => true
                ]
            );

            // Get cities for this province
            $cities = $geographicalService->getCities($provinceData['id']);

            foreach ($cities as $cityData) {
                $city = City::updateOrCreate(
                    ['rajaongkir_id' => $cityData['rajaongkir_id']],
                    [
                        'province_id' => $province->id,
                        'name' => $cityData['name'],
                        'type' => $cityData['type'],
                        'postal_code' => null,
                        'is_active' => true
                    ]
                );

                // Get districts for this city
                $districts = $geographicalService->getDistricts($cityData['id']);

                foreach ($districts as $districtData) {
                    District::updateOrCreate(
                        ['rajaongkir_id' => $districtData['id']],
                        [
                            'city_id' => $city->id,
                            'name' => $districtData['name'],
                            'is_active' => true
                        ]
                    );
                }
            }
        }

        $this->command->info('Geographical data seeded successfully!');
    }
}
