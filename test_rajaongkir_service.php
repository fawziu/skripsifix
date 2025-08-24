<?php
/**
 * Test file untuk RajaOngkirService
 * Jalankan dengan: php test_rajaongkir_service.php
 */

require_once 'vendor/autoload.php';

use App\Services\RajaOngkirService;

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing RajaOngkirService ===\n\n";

try {
    $rajaOngkirService = new RajaOngkirService();

    // Test 1: Check API Key
    echo "1. Testing API Key...\n";
    $apiKey = config('services.rajaongkir.api_key');
    if ($apiKey) {
        echo "   ✅ API Key found: " . substr($apiKey, 0, 10) . "...\n";
    } else {
        echo "   ❌ API Key not found!\n";
    }

    // Test 2: Check Base URL
    echo "\n2. Testing Base URL...\n";
    $baseUrl = config('services.rajaongkir.base_url');
    echo "   Base URL: {$baseUrl}\n";

    // Test 3: Test calculateShippingCost with fallback
    echo "\n3. Testing calculateShippingCost...\n";
    $testData = [
        'origin' => 1, // Jakarta
        'destination' => 2, // Bandung
        'weight' => 1000, // 1kg in grams
        'courier' => 'jne'
    ];

    $result = $rajaOngkirService->calculateShippingCost($testData);

    if (!empty($result)) {
        echo "   ✅ Shipping cost calculated successfully\n";
        echo "   Results:\n";
        foreach ($result as $index => $service) {
            echo "     " . ($index + 1) . ". " . $service['service'] . " - " . $service['description'] . "\n";
            echo "        Cost: Rp " . number_format($service['cost']) . "\n";
            echo "        ETD: " . $service['etd'] . "\n";
            echo "        Note: " . $service['note'] . "\n";
            if (isset($service['is_fallback'])) {
                echo "        ⚠️  This is a fallback estimate\n";
            }
            echo "\n";
        }
    } else {
        echo "   ❌ Failed to calculate shipping cost\n";
    }

    // Test 4: Test getAvailableCouriers
    echo "\n4. Testing getAvailableCouriers...\n";
    $couriers = $rajaOngkirService->getAvailableCouriers();
    echo "   ✅ Found " . count($couriers) . " couriers\n";
    echo "   Sample couriers: " . implode(', ', array_slice($couriers, 0, 5)) . "...\n";

    echo "\n=== Test completed ===\n";

} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
