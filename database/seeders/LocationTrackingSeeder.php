<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\LocationTracking;
use Carbon\Carbon;

class LocationTrackingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some orders for testing
        $orders = Order::whereIn('status', ['assigned', 'picked_up', 'in_transit', 'awaiting_confirmation'])
            ->with(['courier', 'customer'])
            ->limit(5)
            ->get();

        foreach ($orders as $order) {
            if ($order->courier) {
                // Create some courier locations
                $this->createCourierLocations($order);
            }

            if ($order->customer) {
                // Create some customer locations
                $this->createCustomerLocations($order);
            }
        }
    }

    private function createCourierLocations(Order $order)
    {
        // Simulate courier movement from origin to destination
        $originLat = -6.200000 + (rand(-50, 50) / 10000); // Jakarta area
        $originLng = 106.816666 + (rand(-50, 50) / 10000);
        
        $destLat = -6.200000 + (rand(-100, 100) / 10000);
        $destLng = 106.816666 + (rand(-100, 100) / 10000);

        $steps = 10;
        for ($i = 0; $i < $steps; $i++) {
            $progress = $i / ($steps - 1);
            
            $lat = $originLat + ($destLat - $originLat) * $progress;
            $lng = $originLng + ($destLng - $originLng) * $progress;
            
            // Add some randomness
            $lat += (rand(-10, 10) / 100000);
            $lng += (rand(-10, 10) / 100000);

            LocationTracking::create([
                'order_id' => $order->id,
                'user_id' => $order->courier->id,
                'user_type' => 'courier',
                'latitude' => $lat,
                'longitude' => $lng,
                'accuracy' => rand(5, 20),
                'speed' => rand(10, 50),
                'heading' => rand(0, 360),
                'tracked_at' => Carbon::now()->subMinutes(($steps - $i) * 5),
            ]);
        }
    }

    private function createCustomerLocations(Order $order)
    {
        // Customer location (destination) - more stable
        $lat = -6.200000 + (rand(-50, 50) / 10000);
        $lng = 106.816666 + (rand(-50, 50) / 10000);

        // Create 3 customer locations (less frequent updates)
        for ($i = 0; $i < 3; $i++) {
            LocationTracking::create([
                'order_id' => $order->id,
                'user_id' => $order->customer->id,
                'user_type' => 'customer',
                'latitude' => $lat + (rand(-5, 5) / 100000),
                'longitude' => $lng + (rand(-5, 5) / 100000),
                'accuracy' => rand(10, 30),
                'speed' => 0, // Customer is stationary
                'heading' => null,
                'tracked_at' => Carbon::now()->subMinutes(($i + 1) * 15),
            ]);
        }
    }
}
