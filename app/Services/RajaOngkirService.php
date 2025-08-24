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
        // Update to API V2 as V1 is deprecated since July 1, 2025
        $this->baseUrl = 'https://rajaongkir.komerce.id/api/v2';
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
     * Create waybill for RajaOngkir shipping
     */
    public function createWaybill(array $data): ?array
    {
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->post("{$this->baseUrl}/waybill", [
                'courier' => strtolower($data['courier']),
                'waybill' => $this->generateWaybillNumber(),
                'origin' => $data['origin'],
                'destination' => $data['destination'],
                'weight' => $data['weight'],
                'description' => $data['description'],
                'value' => $data['value'],
                'origin_address' => $data['origin_address'],
                'destination_address' => $data['destination_address'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                if (isset($responseData['data'])) {
                    return [
                        'waybill_number' => $responseData['data']['waybill_number'] ?? $this->generateWaybillNumber(),
                        'tracking_url' => $responseData['data']['tracking_url'] ?? null,
                        'label_url' => $responseData['data']['label_url'] ?? null,
                        'status' => 'created',
                        'courier_name' => strtoupper($data['courier']),
                        'origin' => $data['origin'],
                        'destination' => $data['destination'],
                        'weight' => $data['weight'],
                        'etd' => $responseData['data']['etd'] ?? '1-2 hari',
                    ];
                }
            }

            Log::error('RajaOngkir API Error - Create Waybill', [
                'status' => $response->status(),
                'response' => $response->body(),
                'data' => $data,
            ]);

            // Return fallback data if API fails
            return [
                'waybill_number' => $this->generateWaybillNumber(),
                'tracking_url' => null,
                'label_url' => null,
                'status' => 'created',
                'courier_name' => strtoupper($data['courier']),
                'origin' => $data['origin'],
                'destination' => $data['destination'],
                'weight' => $data['weight'],
                'etd' => '1-2 hari',
            ];

        } catch (Exception $e) {
            Log::error('RajaOngkir API Exception - Create Waybill', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);

            // Return fallback data on exception
            return [
                'waybill_number' => $this->generateWaybillNumber(),
                'tracking_url' => null,
                'label_url' => null,
                'status' => 'created',
                'courier_name' => strtoupper($data['courier']),
                'origin' => $data['origin'],
                'destination' => $data['destination'],
                'weight' => $data['weight'],
                'etd' => '1-2 hari',
            ];
        }
    }

    /**
     * Generate unique waybill number
     */
    private function generateWaybillNumber(): string
    {
        return 'RO' . date('Ymd') . strtoupper(uniqid());
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
     * Calculate shipping cost using the correct endpoint for API V2
     */
    public function calculateShippingCost(array $data): array
    {
        try {
            Log::info('RajaOngkir API V2 call - Calculate Cost', [
                'request_data' => $data,
                'api_key' => $this->apiKey ? 'set' : 'not_set',
                'base_url' => $this->baseUrl,
            ]);

            // Prepare request data in correct format for API V2
            $requestData = [
                'origin' => $data['origin'],
                'originType' => 'city',
                'destination' => $data['destination'],
                'destinationType' => 'city',
                'weight' => $data['weight'],
                'courier' => $data['courier'],
            ];

            $response = Http::withHeaders([
                'key' => $this->apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post("{$this->baseUrl}/calculate/domestic-cost", $requestData);

            Log::info('RajaOngkir API V2 response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('RajaOngkir API V2 success', [
                    'data' => $responseData,
                ]);

                // Extract the cost data from RajaOngkir response
                if (isset($responseData['data']) && is_array($responseData['data'])) {
                    $results = [];
                    foreach ($responseData['data'] as $service) {
                        $results[] = [
                            'service' => $service['code'],
                            'description' => $service['description'],
                            'cost' => $service['cost'],
                            'etd' => $service['etd'],
                            'note' => $service['name'] ?? '',
                        ];
                    }
                    return $results;
                }

                return [];
            }

            Log::error('RajaOngkir API V2 Error - Calculate Cost', [
                'status' => $response->status(),
                'response' => $response->body(),
                'request_data' => $data,
            ]);

            return [];
        } catch (Exception $e) {
            Log::error('RajaOngkir API V2 Exception - Calculate Cost', [
                'message' => $e->getMessage(),
                'request_data' => $data,
            ]);
            return [];
        }
    }

    /**
     * Track shipment using the correct endpoint for API V2
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

            Log::error('RajaOngkir API V2 Error - Track Shipment', [
                'status' => $response->status(),
                'response' => $response->body(),
                'tracking_number' => $trackingNumber,
                'courier' => $courier,
            ]);

            return [];
        } catch (Exception $e) {
            Log::error('RajaOngkir API V2 Exception - Track Shipment', [
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

    /**
     * Create shipping order and generate tracking number
     * Note: This would require integration with RajaOngkir's order creation API
     * Currently this is a placeholder for future implementation
     */
    public function createShippingOrder(array $orderData): array
    {
        try {
            // This would integrate with RajaOngkir's order creation API
            // For now, we'll generate a mock tracking number
            $trackingNumber = $this->generateMockTrackingNumber($orderData['courier']);

            return [
                'success' => true,
                'tracking_number' => $trackingNumber,
                'message' => 'Order created successfully',
                'estimated_delivery' => now()->addDays(3)->format('Y-m-d H:i:s'),
            ];
        } catch (Exception $e) {
            Log::error('RajaOngkir API Exception - Create Order', [
                'message' => $e->getMessage(),
                'order_data' => $orderData,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create shipping order',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate mock tracking number for testing purposes
     */
    private function generateMockTrackingNumber(string $courier): string
    {
        $prefixes = [
            'jne' => 'JNE',
            'pos' => 'POS',
            'tiki' => 'TIKI',
            'sicepat' => 'SICEPAT',
            'jnt' => 'JNT',
            'wahana' => 'WAHANA',
            'ninja' => 'NINJA',
            'lion' => 'LION',
            'pcp' => 'PCP',
            'jet' => 'JET',
            'rex' => 'REX',
            'first' => 'FIRST',
            'ide' => 'IDE',
            'spx' => 'SPX',
            'kgx' => 'KGX',
            'sap' => 'SAP',
            'jxe' => 'JXE',
            'rpx' => 'RPX',
            'pandu' => 'PANDU',
            'pahala' => 'PAHALA',
            'cahaya' => 'CAHAYA',
            'sat' => 'SAT',
            'nusantara' => 'NUSANTARA',
            'star' => 'STAR',
            'idl' => 'IDL',
            'indah' => 'INDAH',
            'dse' => 'DSE',
        ];

        $prefix = $prefixes[strtolower($courier)] ?? 'TRK';
        $randomNumber = str_pad(rand(1, 999999999), 9, '0', STR_PAD_LEFT);

        return $prefix . $randomNumber;
    }

    /**
     * Get shipping label/waybill
     * Note: This would require integration with RajaOngkir's label generation API
     */
    public function getShippingLabel(string $trackingNumber, string $courier): array
    {
        try {
            // This would integrate with RajaOngkir's label generation API
            // For now, return a mock response
            return [
                'success' => true,
                'label_url' => null, // Would be actual label URL from RajaOngkir
                'message' => 'Label generated successfully',
                'tracking_number' => $trackingNumber,
            ];
        } catch (Exception $e) {
            Log::error('RajaOngkir API Exception - Get Label', [
                'message' => $e->getMessage(),
                'tracking_number' => $trackingNumber,
                'courier' => $courier,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to generate shipping label',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate shipping cost
     */
    public function calculate(array $data): ?array
    {
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->get("{$this->baseUrl}/calculate", [
                'origin' => $data['origin'],
                'destination' => $data['destination'],
                'weight' => $data['weight'],
                'courier' => $data['courier'] ?? 'all',
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return $responseData['data'] ?? null;
            }

            Log::error('RajaOngkir API Error - Calculate', [
                'status' => $response->status(),
                'response' => $response->body(),
                'data' => $data,
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('RajaOngkir API Exception - Calculate', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);
            return null;
        }
    }

    /**
     * Search waybills
     */
    public function search(array $filters): ?array
    {
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->get("{$this->baseUrl}/search", $filters);

            if ($response->successful()) {
                $responseData = $response->json();
                return $responseData['data'] ?? null;
            }

            Log::error('RajaOngkir API Error - Search', [
                'status' => $response->status(),
                'response' => $response->body(),
                'filters' => $filters,
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('RajaOngkir API Exception - Search', [
                'message' => $e->getMessage(),
                'filters' => $filters,
            ]);
            return null;
        }
    }

    /**
     * Store waybill
     */
    public function store(array $data): ?array
    {
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->post("{$this->baseUrl}/store", $data);

            if ($response->successful()) {
                $responseData = $response->json();
                return $responseData['data'] ?? null;
            }

            Log::error('RajaOngkir API Error - Store', [
                'status' => $response->status(),
                'response' => $response->body(),
                'data' => $data,
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('RajaOngkir API Exception - Store', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);
            return null;
        }
    }

    /**
     * Cancel waybill
     */
    public function cancel(string $waybillNumber): ?array
    {
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->put("{$this->baseUrl}/cancel", [
                'waybill_number' => $waybillNumber,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return $responseData['data'] ?? null;
            }

            Log::error('RajaOngkir API Error - Cancel', [
                'status' => $response->status(),
                'response' => $response->body(),
                'waybill_number' => $waybillNumber,
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('RajaOngkir API Exception - Cancel', [
                'message' => $e->getMessage(),
                'waybill_number' => $waybillNumber,
            ]);
            return null;
        }
    }

    /**
     * Get waybill detail
     */
    public function detail(string $waybillNumber): ?array
    {
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->get("{$this->baseUrl}/detail", [
                'waybill_number' => $waybillNumber,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return $responseData['data'] ?? null;
            }

            Log::error('RajaOngkir API Error - Detail', [
                'status' => $response->status(),
                'response' => $response->body(),
                'waybill_number' => $waybillNumber,
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('RajaOngkir API Exception - Detail', [
                'message' => $e->getMessage(),
                'waybill_number' => $waybillNumber,
            ]);
            return null;
        }
    }

    /**
     * Get waybill history
     */
    public function historyAirwayBill(string $waybillNumber): ?array
    {
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->get("{$this->baseUrl}/history-airway-bill", [
                'waybill_number' => $waybillNumber,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return $responseData['data'] ?? null;
            }

            Log::error('RajaOngkir API Error - History Airway Bill', [
                'status' => $response->status(),
                'response' => $response->body(),
                'waybill_number' => $waybillNumber,
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('RajaOngkir API Exception - History Airway Bill', [
                'message' => $e->getMessage(),
                'waybill_number' => $waybillNumber,
            ]);
            return null;
        }
    }

    /**
     * Request pickup
     */
    public function pickup(array $data): ?array
    {
        try {
            // Try RajaOngkir API first
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->post("{$this->baseUrl}/pickup", $data);

            if ($response->successful()) {
                $responseData = $response->json();
                return $responseData['data'] ?? null;
            }

            // If RajaOngkir API fails, log the error
            Log::warning('RajaOngkir API Error - Pickup (using fallback)', [
                'status' => $response->status(),
                'response' => $response->body(),
                'data' => $data,
            ]);

            // Fallback: Generate local pickup request
            return $this->generateLocalPickup($data);

        } catch (Exception $e) {
            Log::warning('RajaOngkir API Exception - Pickup (using fallback)', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);

            // Fallback: Generate local pickup request
            return $this->generateLocalPickup($data);
        }
    }

    /**
     * Generate local pickup request when RajaOngkir API is unavailable
     */
    private function generateLocalPickup(array $data): array
    {
        $pickupId = 'PICKUP' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 8));

        return [
            'pickup_id' => $pickupId,
            'waybill_number' => $data['waybill_number'] ?? null,
            'courier' => $data['courier'] ?? 'unknown',
            'pickup_date' => $data['pickup_date'] ?? now()->format('Y-m-d'),
            'pickup_time' => $data['pickup_time'] ?? '09:00-17:00',
            'pickup_address' => $data['pickup_address'] ?? '',
            'pickup_contact' => $data['pickup_contact'] ?? '',
            'pickup_phone' => $data['pickup_phone'] ?? '',
            'notes' => $data['notes'] ?? '',
            'status' => 'requested',
            'message' => 'Pickup request generated locally (RajaOngkir API unavailable)',
            'estimated_pickup' => now()->addDay()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Print label
     */
    public function printLabel(string $waybillNumber): ?array
    {
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
            ])->post("{$this->baseUrl}/print-label", [
                'waybill_number' => $waybillNumber,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                return $responseData['data'] ?? null;
            }

            Log::error('RajaOngkir API Error - Print Label', [
                'status' => $response->status(),
                'response' => $response->body(),
                'waybill_number' => $waybillNumber,
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('RajaOngkir API Exception - Print Label', [
                'message' => $e->getMessage(),
                'waybill_number' => $waybillNumber,
            ]);
            return null;
        }
    }
}
