<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class RajaOngkirService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.api_key');
        $this->baseUrl = 'https://rajaongkir.komerce.id/api/v1';
    }

    /**
     * Search domestic destinations using Direct Search Method
     */
    public function searchDestinations(string $search, int $limit = 10, int $offset = 0): array
    {
        $cacheKey = "rajaongkir_destinations_{$search}_{$limit}_{$offset}";
        
        return Cache::remember($cacheKey, 86400, function () use ($search, $limit, $offset) {
            try {
                $response = Http::withHeaders([
                    'key' => $this->apiKey,
                ])->get("{$this->baseUrl}/destination/domestic-destination", [
                    'search' => $search,
                    'limit' => $limit,
                    'offset' => $offset,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['data'] ?? [];
                }

                Log::error('RajaOngkir API Error - Search Destinations', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'search' => $search,
                ]);

                return [];
            } catch (Exception $e) {
                Log::error('RajaOngkir API Exception - Search Destinations', [
                    'message' => $e->getMessage(),
                    'search' => $search,
                ]);
                return [];
            }
        });
    }

    /**
     * Get provinces with caching for specific provinces only
     */
    public function getProvinces(): array
    {
        $cacheKey = 'rajaongkir_provinces_filtered';
        
        return Cache::remember($cacheKey, 86400, function () {
            try {
                $response = Http::withHeaders([
                    'key' => $this->apiKey,
                ])->get("{$this->baseUrl}/destination/province");

                if ($response->successful()) {
                    $data = $response->json();
                    $allProvinces = $data['data'] ?? [];
                    
                    // Filter only Sulawesi Selatan and Papua Barat
                    $filteredProvinces = array_filter($allProvinces, function ($province) {
                        $allowedProvinces = ['Sulawesi Selatan', 'Papua Barat'];
                        return in_array($province['province_name'], $allowedProvinces);
                    });
                    
                    return array_values($filteredProvinces);
                }

                Log::error('RajaOngkir API Error - Get Provinces', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return [];
            } catch (Exception $e) {
                Log::error('RajaOngkir API Exception - Get Provinces', [
                    'message' => $e->getMessage(),
                ]);
                return [];
            }
        });
    }

    /**
     * Get cities by province with caching for specific provinces
     */
    public function getCities(int $provinceId = null): array
    {
        $cacheKey = "rajaongkir_cities_{$provinceId}";
        
        return Cache::remember($cacheKey, 86400, function () use ($provinceId) {
            try {
                $url = "{$this->baseUrl}/destination/city";
                if ($provinceId) {
                    $url .= "?province={$provinceId}";
                }

                $response = Http::withHeaders([
                    'key' => $this->apiKey,
                ])->get($url);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['data'] ?? [];
                }

                Log::error('RajaOngkir API Error - Get Cities', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'province_id' => $provinceId,
                ]);

                return [];
            } catch (Exception $e) {
                Log::error('RajaOngkir API Exception - Get Cities', [
                    'message' => $e->getMessage(),
                    'province_id' => $provinceId,
                ]);
                return [];
            }
        });
    }

    /**
     * Get districts by city with caching for specific provinces
     */
    public function getDistricts(int $cityId): array
    {
        $cacheKey = "rajaongkir_districts_{$cityId}";
        
        return Cache::remember($cacheKey, 86400, function () use ($cityId) {
            try {
                $response = Http::withHeaders([
                    'key' => $this->apiKey,
                ])->get("{$this->baseUrl}/destination/subdistrict?city={$cityId}");

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['data'] ?? [];
                }

                Log::error('RajaOngkir API Error - Get Districts', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'city_id' => $cityId,
                ]);

                return [];
            } catch (Exception $e) {
                Log::error('RajaOngkir API Exception - Get Districts', [
                    'message' => $e->getMessage(),
                    'city_id' => $cityId,
                ]);
                return [];
            }
        });
    }

    /**
     * Calculate shipping cost using the correct endpoint
     */
    public function calculateShippingCost(array $data): array
    {
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
                'content-type' => 'application/json',
            ])->post("{$this->baseUrl}/calculate/domestic-cost", [
                'origin' => $data['origin'],
                'destination' => $data['destination'],
                'weight' => $data['weight'],
                'courier' => $data['courier'],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? [];
            }

            Log::error('RajaOngkir API Error - Calculate Cost', [
                'status' => $response->status(),
                'response' => $response->body(),
                'request_data' => $data,
            ]);

            return [];
        } catch (Exception $e) {
            Log::error('RajaOngkir API Exception - Calculate Cost', [
                'message' => $e->getMessage(),
                'request_data' => $data,
            ]);
            return [];
        }
    }

    /**
     * Track shipment using the correct endpoint
     */
    public function trackShipment(string $trackingNumber, string $courier, string $lastPhoneNumber = null): array
    {
        try {
            $requestData = [
                'waybill' => $trackingNumber,
                'courier' => $courier,
            ];

            // Add last_phone_number for JNE courier
            if ($courier === 'jne' && $lastPhoneNumber) {
                $requestData['last_phone_number'] = $lastPhoneNumber;
            }

            $response = Http::withHeaders([
                'key' => $this->apiKey,
                'content-type' => 'application/json',
            ])->post("{$this->baseUrl}/track/waybill", $requestData);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? [];
            }

            Log::error('RajaOngkir API Error - Track Shipment', [
                'status' => $response->status(),
                'response' => $response->body(),
                'tracking_number' => $trackingNumber,
                'courier' => $courier,
            ]);

            return [];
        } catch (Exception $e) {
            Log::error('RajaOngkir API Exception - Track Shipment', [
                'message' => $e->getMessage(),
                'tracking_number' => $trackingNumber,
                'courier' => $courier,
            ]);
            return [];
        }
    }

    /**
     * Get available couriers
     */
    public function getAvailableCouriers(): array
    {
        return [
            'jne' => 'JNE',
            'pos' => 'POS Indonesia',
            'tiki' => 'TIKI',
            'sicepat' => 'SiCepat',
            'jnt' => 'J&T Express',
            'wahana' => 'Wahana',
            'ninja' => 'Ninja Express',
            'lion' => 'Lion Parcel',
            'pcp' => 'PCP Express',
            'jet' => 'JET Express',
            'rex' => 'REX Express',
            'first' => 'First Logistics',
            'ide' => 'ID Express',
            'spx' => 'Shopee Express',
            'kgx' => 'KGXpress',
            'sap' => 'SAP Express',
            'jxe' => 'JX Express',
            'rpx' => 'RPX Express',
            'pandu' => 'Pandu Logistics',
            'pahala' => 'Pahala Express',
            'cahaya' => 'Cahaya Logistik',
            'sat' => 'Sat Express',
            'nusantara' => 'Nusantara Card Semesta',
            'star' => 'Star Cargo',
            'idl' => 'IDL Cargo',
            'indah' => 'Indah Cargo',
            'dse' => '21 Express',
        ];
    }

    /**
     * Clear cache for specific provinces only
     */
    public function clearCache(): void
    {
        Cache::forget('rajaongkir_provinces_filtered');
        Cache::forget('rajaongkir_destinations_*');
        Cache::forget('rajaongkir_cities_*');
        Cache::forget('rajaongkir_districts_*');
    }

    /**
     * Get cached destinations for specific provinces
     */
    public function getCachedDestinationsForProvinces(): array
    {
        $sulawesiSelatanCities = [
            'Makassar', 'Gowa', 'Maros', 'Pangkajene dan Kepulauan', 'Barru', 'Bone', 'Soppeng', 
            'Wajo', 'Sidenreng Rappang', 'Parepare', 'Pinrang', 'Enrekang', 'Tana Toraja', 
            'Luwu', 'Luwu Utara', 'Luwu Timur', 'Toraja Utara', 'Bantaeng', 'Bulukumba', 
            'Sinjai', 'Selayar', 'Takalar', 'Jeneponto'
        ];

        $papuaBaratCities = [
            'Manokwari', 'Sorong', 'Fakfak', 'Sorong Selatan', 'Raja Ampat', 'Teluk Bintuni', 
            'Teluk Wondama', 'Kaimana', 'Tambrauw', 'Maybrat', 'Manokwari Selatan', 'Pegunungan Arfak'
        ];

        $cachedDestinations = [];

        // Cache destinations for Sulawesi Selatan
        foreach ($sulawesiSelatanCities as $city) {
            $destinations = $this->searchDestinations($city, 5, 0);
            if (!empty($destinations)) {
                $cachedDestinations['sulawesi_selatan'][$city] = $destinations;
            }
        }

        // Cache destinations for Papua Barat
        foreach ($papuaBaratCities as $city) {
            $destinations = $this->searchDestinations($city, 5, 0);
            if (!empty($destinations)) {
                $cachedDestinations['papua_barat'][$city] = $destinations;
            }
        }

        return $cachedDestinations;
    }
}
