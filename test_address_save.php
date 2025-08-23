<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Address;
use App\Services\LocalGeographicalService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Testing address save...\n";
    
    // Test LocalGeographicalService
    $geoService = new LocalGeographicalService();
    $provinces = $geoService->getProvinces();
    echo "Provinces loaded: " . count($provinces) . "\n";
    
    // Test getting a user
    $user = User::first();
    if (!$user) {
        echo "No users found in database\n";
        exit;
    }
    echo "User found: " . $user->name . "\n";
    
    // Test creating address
    $addressData = [
        'user_id' => $user->id,
        'type' => 'home',
        'label' => 'Test Address',
        'recipient_name' => 'Test Recipient',
        'phone' => '081234567890',
        'province_id' => 648,
        'city_id' => 648,
        'district_id' => 6729,
        'postal_code' => '12345',
        'address_line' => 'Jl. Test No. 123',
        'is_primary' => true,
        'is_active' => true,
    ];
    
    $address = new Address($addressData);
    $result = $address->save();
    
    if ($result) {
        echo "Address saved successfully! ID: " . $address->id . "\n";
        $address->delete(); // Clean up
        echo "Test address deleted\n";
    } else {
        echo "Failed to save address\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
