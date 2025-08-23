<?php

namespace App\Services;

use App\Http\Controllers\DataDistrikSulSel;
use App\Http\Controllers\DataDistrikPapuaBarat;

class LocalGeographicalService
{
    private DataDistrikSulSel $sulSelData;
    private DataDistrikPapuaBarat $papuaBaratData;

    public function __construct()
    {
        $this->sulSelData = new DataDistrikSulSel();
        $this->papuaBaratData = new DataDistrikPapuaBarat();
    }

    /**
     * Get provinces (Sulawesi Selatan and Papua Barat)
     */
    public function getProvinces(): array
    {
        return [
            [
                'id' => 648,
                'name' => 'Sulawesi Selatan',
                'rajaongkir_id' => 648
            ],
            [
                'id' => 546,
                'name' => 'Papua Barat',
                'rajaongkir_id' => 546
            ]
        ];
    }

    /**
     * Get cities by province
     */
    public function getCities(int $provinceId = null): array
    {
        if ($provinceId === 648) {
            // Sulawesi Selatan cities
            return [
                ['id' => 648, 'name' => 'Makassar', 'type' => 'Kota', 'rajaongkir_id' => 648],
                ['id' => 649, 'name' => 'Bantaeng', 'type' => 'Kabupaten', 'rajaongkir_id' => 649],
                ['id' => 650, 'name' => 'Barru', 'type' => 'Kabupaten', 'rajaongkir_id' => 650],
                ['id' => 651, 'name' => 'Bulukumba', 'type' => 'Kabupaten', 'rajaongkir_id' => 651],
                ['id' => 652, 'name' => 'Enrekang', 'type' => 'Kabupaten', 'rajaongkir_id' => 652],
                ['id' => 653, 'name' => 'Jeneponto', 'type' => 'Kabupaten', 'rajaongkir_id' => 653],
                ['id' => 654, 'name' => 'Tana Toraja', 'type' => 'Kabupaten', 'rajaongkir_id' => 654],
                ['id' => 655, 'name' => 'Toraja Utara', 'type' => 'Kabupaten', 'rajaongkir_id' => 655],
                ['id' => 659, 'name' => 'Maros', 'type' => 'Kabupaten', 'rajaongkir_id' => 659],
                ['id' => 663, 'name' => 'Palopo', 'type' => 'Kota', 'rajaongkir_id' => 663],
                ['id' => 664, 'name' => 'Pinrang', 'type' => 'Kabupaten', 'rajaongkir_id' => 664],
                ['id' => 667, 'name' => 'Rappang', 'type' => 'Kabupaten', 'rajaongkir_id' => 667],
                ['id' => 670, 'name' => 'Wajo', 'type' => 'Kabupaten', 'rajaongkir_id' => 670],
                ['id' => 671, 'name' => 'Sinjai', 'type' => 'Kabupaten', 'rajaongkir_id' => 671],
                ['id' => 673, 'name' => 'Gowa', 'type' => 'Kabupaten', 'rajaongkir_id' => 673],
                ['id' => 674, 'name' => 'Takalar', 'type' => 'Kabupaten', 'rajaongkir_id' => 674],
                ['id' => 676, 'name' => 'Bone', 'type' => 'Kabupaten', 'rajaongkir_id' => 676],
                ['id' => 678, 'name' => 'Soppeng', 'type' => 'Kabupaten', 'rajaongkir_id' => 678],
                ['id' => 679, 'name' => 'Selayar', 'type' => 'Kabupaten', 'rajaongkir_id' => 679],
                ['id' => 680, 'name' => 'Pangkep', 'type' => 'Kabupaten', 'rajaongkir_id' => 680],
                ['id' => 683, 'name' => 'Parepare', 'type' => 'Kota', 'rajaongkir_id' => 683],
                ['id' => 684, 'name' => 'Luwu', 'type' => 'Kabupaten', 'rajaongkir_id' => 684],
                ['id' => 685, 'name' => 'Luwu Timur', 'type' => 'Kabupaten', 'rajaongkir_id' => 685],
                ['id' => 686, 'name' => 'Luwu Utara', 'type' => 'Kabupaten', 'rajaongkir_id' => 686]
            ];
        } elseif ($provinceId === 546) {
            // Papua Barat cities
            return [
                ['id' => 546, 'name' => 'Sorong', 'type' => 'Kota', 'rajaongkir_id' => 546],
                ['id' => 547, 'name' => 'Tambraw', 'type' => 'Kabupaten', 'rajaongkir_id' => 547],
                ['id' => 548, 'name' => 'Fakfak', 'type' => 'Kabupaten', 'rajaongkir_id' => 548],
                ['id' => 549, 'name' => 'Kaimana', 'type' => 'Kabupaten', 'rajaongkir_id' => 549],
                ['id' => 551, 'name' => 'Raja Ampat', 'type' => 'Kabupaten', 'rajaongkir_id' => 551],
                ['id' => 552, 'name' => 'Sorong Selatan', 'type' => 'Kabupaten', 'rajaongkir_id' => 552],
                ['id' => 553, 'name' => 'Maybrat', 'type' => 'Kabupaten', 'rajaongkir_id' => 553],
                ['id' => 554, 'name' => 'Teluk Bintuni', 'type' => 'Kabupaten', 'rajaongkir_id' => 554],
                ['id' => 556, 'name' => 'Teluk Wondama', 'type' => 'Kabupaten', 'rajaongkir_id' => 556],
                ['id' => 557, 'name' => 'Manokwari', 'type' => 'Kabupaten', 'rajaongkir_id' => 557],
                ['id' => 558, 'name' => 'Pegunungan Arfak', 'type' => 'Kabupaten', 'rajaongkir_id' => 558],
                ['id' => 559, 'name' => 'Manokwari Selatan', 'type' => 'Kabupaten', 'rajaongkir_id' => 559]
            ];
        }

        // Return all cities if no province specified
        return array_merge(
            $this->getCities(648),
            $this->getCities(546)
        );
    }

    /**
     * Get districts by city
     */
    public function getDistricts(int $cityId): array
    {
        // Try Sulawesi Selatan data first
        $sulSelDistricts = $this->sulSelData->getData($cityId);
        if ($sulSelDistricts !== null) {
            return $sulSelDistricts;
        }

        // Try Papua Barat data
        $papuaBaratDistricts = $this->papuaBaratData->getData($cityId);
        if ($papuaBaratDistricts !== null) {
            return $papuaBaratDistricts;
        }

        // Return empty array if no districts found
        return [];
    }

    /**
     * Get all available destinations for shipping
     */
    public function getCachedDestinationsForProvinces(): array
    {
        $sulawesiSelatanCities = [
            ['id' => 648, 'name' => 'Makassar', 'type' => 'Kota'],
            ['id' => 649, 'name' => 'Bantaeng', 'type' => 'Kabupaten'],
            ['id' => 650, 'name' => 'Barru', 'type' => 'Kabupaten'],
            ['id' => 651, 'name' => 'Bulukumba', 'type' => 'Kabupaten'],
            ['id' => 652, 'name' => 'Enrekang', 'type' => 'Kabupaten'],
            ['id' => 653, 'name' => 'Jeneponto', 'type' => 'Kabupaten'],
            ['id' => 654, 'name' => 'Tana Toraja', 'type' => 'Kabupaten'],
            ['id' => 655, 'name' => 'Toraja Utara', 'type' => 'Kabupaten'],
            ['id' => 659, 'name' => 'Maros', 'type' => 'Kabupaten'],
            ['id' => 663, 'name' => 'Palopo', 'type' => 'Kota'],
            ['id' => 664, 'name' => 'Pinrang', 'type' => 'Kabupaten'],
            ['id' => 667, 'name' => 'Rappang', 'type' => 'Kabupaten'],
            ['id' => 670, 'name' => 'Wajo', 'type' => 'Kabupaten'],
            ['id' => 671, 'name' => 'Sinjai', 'type' => 'Kabupaten'],
            ['id' => 673, 'name' => 'Gowa', 'type' => 'Kabupaten'],
            ['id' => 674, 'name' => 'Takalar', 'type' => 'Kabupaten'],
            ['id' => 676, 'name' => 'Bone', 'type' => 'Kabupaten'],
            ['id' => 678, 'name' => 'Soppeng', 'type' => 'Kabupaten'],
            ['id' => 679, 'name' => 'Selayar', 'type' => 'Kabupaten'],
            ['id' => 680, 'name' => 'Pangkep', 'type' => 'Kabupaten'],
            ['id' => 683, 'name' => 'Parepare', 'type' => 'Kota'],
            ['id' => 684, 'name' => 'Luwu', 'type' => 'Kabupaten'],
            ['id' => 685, 'name' => 'Luwu Timur', 'type' => 'Kabupaten'],
            ['id' => 686, 'name' => 'Luwu Utara', 'type' => 'Kabupaten']
        ];

        $papuaBaratCities = [
            ['id' => 546, 'name' => 'Sorong', 'type' => 'Kota'],
            ['id' => 547, 'name' => 'Tambraw', 'type' => 'Kabupaten'],
            ['id' => 548, 'name' => 'Fakfak', 'type' => 'Kabupaten'],
            ['id' => 549, 'name' => 'Kaimana', 'type' => 'Kabupaten'],
            ['id' => 551, 'name' => 'Raja Ampat', 'type' => 'Kabupaten'],
            ['id' => 552, 'name' => 'Sorong Selatan', 'type' => 'Kabupaten'],
            ['id' => 553, 'name' => 'Maybrat', 'type' => 'Kabupaten'],
            ['id' => 554, 'name' => 'Teluk Bintuni', 'type' => 'Kabupaten'],
            ['id' => 556, 'name' => 'Teluk Wondama', 'type' => 'Kabupaten'],
            ['id' => 557, 'name' => 'Manokwari', 'type' => 'Kabupaten'],
            ['id' => 558, 'name' => 'Pegunungan Arfak', 'type' => 'Kabupaten'],
            ['id' => 559, 'name' => 'Manokwari Selatan', 'type' => 'Kabupaten']
        ];

        $destinations = [];

        // Add Sulawesi Selatan cities with districts
        foreach ($sulawesiSelatanCities as $city) {
            $districts = $this->getDistricts($city['id']);
            $destinations[] = [
                'city' => $city,
                'districts' => $districts
            ];
        }

        // Add Papua Barat cities with districts
        foreach ($papuaBaratCities as $city) {
            $districts = $this->getDistricts($city['id']);
            $destinations[] = [
                'city' => $city,
                'districts' => $districts
            ];
        }

        return $destinations;
    }
}
