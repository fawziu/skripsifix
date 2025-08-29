<?php

require_once 'vendor/autoload.php';

use Carbon\Carbon;

// Test timezone handling
echo "=== Timezone Test ===\n";
echo "Default timezone: " . date_default_timezone_get() . "\n";
echo "Current server time: " . date('Y-m-d H:i:s T') . "\n";

// Test Carbon with WITA
$witaTime = Carbon::now('Asia/Makassar');
echo "Current WITA time: " . $witaTime->format('Y-m-d H:i:s T') . "\n";

// Test pickup time parsing
$pickupDate = '2025-01-27';
$pickupTime = '08:00-10:00';
$timeRange = explode('-', $pickupTime);
$startTime = $timeRange[0];
$endTime = $timeRange[1];

$pickupStart = Carbon::parse($pickupDate . ' ' . $startTime, 'Asia/Makassar');
$pickupEnd = Carbon::parse($pickupDate . ' ' . $endTime, 'Asia/Makassar');

echo "\n=== Pickup Time Test ===\n";
echo "Pickup date: $pickupDate\n";
echo "Pickup time: $pickupTime\n";
echo "Start time: " . $pickupStart->format('Y-m-d H:i:s T') . "\n";
echo "End time: " . $pickupEnd->format('Y-m-d H:i:s T') . "\n";

// Test if current time is within pickup window
$isDue = $witaTime->between($pickupStart->subMinutes(3), $pickupEnd->addMinutes(3));
echo "Is pickup due now: " . ($isDue ? 'Yes' : 'No') . "\n";

echo "\n=== Time Comparison ===\n";
echo "Current time: " . $witaTime->format('Y-m-d H:i:s T') . "\n";
echo "Pickup start (with 3min buffer): " . $pickupStart->subMinutes(3)->format('Y-m-d H:i:s T') . "\n";
echo "Pickup end (with 3min buffer): " . $pickupEnd->addMinutes(3)->format('Y-m-d H:i:s T') . "\n";
