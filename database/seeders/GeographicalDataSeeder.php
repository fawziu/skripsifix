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
        // Hanya ambil data untuk Papua dan Sulawesi Selatan
        $targetProvinces = [
            ['rajaongkir_id' => 26, 'id' => 26, 'name' => 'Papua'],
            ['rajaongkir_id' => 28, 'id' => 28, 'name' => 'Papua Barat'],
            ['rajaongkir_id' => 27, 'id' => 27, 'name' => 'Papua Selatan'],
            ['rajaongkir_id' => 29, 'id' => 29, 'name' => 'Papua Tengah'],
            ['rajaongkir_id' => 30, 'id' => 30, 'name' => 'Papua Pegunungan'],
            ['rajaongkir_id' => 28, 'id' => 28, 'name' => 'Sulawesi Selatan'],
        ];

        foreach ($targetProvinces as $provinceData) {
            $province = Province::updateOrCreate(
                ['rajaongkir_id' => $provinceData['rajaongkir_id']],
                [
                    'name' => $provinceData['name'],
                    'is_active' => true
                ]
            );

            // Get cities for this province
            $cities = $geographicalService->getCities($provinceData['id']);

            // Filter kota-kota yang relevan
            $relevantCities = [];
            if ($provinceData['name'] === 'Sulawesi Selatan') {
                // Hanya kota Makassar dan sekitarnya
                $relevantCities = array_filter($cities, function($city) {
                    return in_array($city['name'], [
                        'Makassar',
                        'Gowa',
                        'Maros',
                        'Takalar',
                        'Pangkajene Kepulauan'
                    ]);
                });
            } elseif (str_contains($provinceData['name'], 'Papua')) {
                // Kota-kota utama di Papua
                $relevantCities = array_filter($cities, function($city) {
                    return in_array($city['name'], [
                        'Jayapura',
                        'Merauke',
                        'Timika',
                        'Sorong',
                        'Manokwari',
                        'Nabire',
                        'Biak Numfor',
                        'Jayawijaya',
                        'Mimika',
                        'Keerom'
                    ]);
                });
            }

            foreach ($relevantCities as $cityData) {
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

                // Filter kecamatan yang relevan
                $relevantDistricts = [];
                if ($cityData['name'] === 'Makassar') {
                    // Kecamatan di Makassar
                    $relevantDistricts = array_filter($districts, function($district) {
                        return in_array($district['name'], [
                            'Panakkukang',
                            'Makassar',
                            'Mariso',
                            'Mamajang',
                            'Tamalate',
                            'Rappocini',
                            'Tamalanrea',
                            'Biringkanaya',
                            'Manggala',
                            'Tallo',
                            'Ujung Pandang',
                            'Wajo',
                            'Bontoala',
                            'Ujung Tanah'
                        ]);
                    });
                } else {
                    // Untuk kota lain, ambil semua kecamatan
                    $relevantDistricts = $districts;
                }

                foreach ($relevantDistricts as $districtData) {
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
