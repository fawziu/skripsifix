<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\User;
use App\Models\Address;
use App\Models\OrderStatus;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users and addresses
        $customers = User::whereHas('role', function($query) {
            $query->where('name', 'customer');
        })->get();

        $couriers = User::whereHas('role', function($query) {
            $query->where('name', 'courier');
        })->get();

        $admins = User::whereHas('role', function($query) {
            $query->where('name', 'admin');
        })->get();

        if ($customers->isEmpty()) {
            $this->command->warn('Skipping OrderSeeder: No customers found.');
            return;
        }

        $itemDescriptions = [
            'Paket pakaian dan aksesoris',
            'Elektronik dan gadget',
            'Makanan dan minuman',
            'Dokumen penting',
            'Barang pecah belah',
            'Peralatan rumah tangga',
            'Buku dan alat tulis',
            'Mainan anak-anak',
            'Peralatan olahraga',
            'Kosmetik dan skincare'
        ];

        $shippingMethods = ['manual', 'rajaongkir'];
        $paymentMethods = ['cod', 'transfer'];
        $statuses = ['pending', 'confirmed', 'assigned', 'picked_up', 'in_transit', 'delivered'];

        foreach ($customers as $customer) {
            // Create 3-8 orders per customer
            $numOrders = rand(3, 8);

            for ($i = 0; $i < $numOrders; $i++) {
                $shippingMethod = $shippingMethods[array_rand($shippingMethods)];
                $paymentMethod = $shippingMethod === 'manual' ? $paymentMethods[array_rand($paymentMethods)] : null;

                // Get customer addresses
                $customerAddresses = $customer->addresses()->active()->get();
                if ($customerAddresses->isEmpty()) {
                    continue;
                }

                $originAddress = $customerAddresses->random();
                $destinationAddress = $customerAddresses->random();

                // Ensure different addresses for origin and destination
                while ($originAddress->id === $destinationAddress->id && $customerAddresses->count() > 1) {
                    $destinationAddress = $customerAddresses->random();
                }

                // Generate order data
                $itemWeight = rand(1, 50) / 10; // 0.1 to 5.0 kg
                $itemPrice = rand(50000, 2000000); // 50k to 2M
                $shippingCost = $this->calculateShippingCost($itemWeight, $shippingMethod);
                $serviceFee = $shippingMethod === 'manual' ? 3000 : 5000;
                $totalAmount = $itemPrice + $shippingCost + $serviceFee;

                // Determine status and assign courier/admin accordingly
                $status = $statuses[array_rand($statuses)];
                $courierId = null;
                $adminId = null;

                if ($status === 'pending') {
                    // No courier or admin assigned yet
                } elseif (in_array($status, ['confirmed', 'assigned'])) {
                    $adminId = $admins->isNotEmpty() ? $admins->random()->id : null;
                    if ($status === 'assigned' && $couriers->isNotEmpty()) {
                        $courierId = $couriers->random()->id;
                    }
                } elseif (in_array($status, ['picked_up', 'in_transit', 'delivered'])) {
                    $adminId = $admins->isNotEmpty() ? $admins->random()->id : null;
                    $courierId = $couriers->isNotEmpty() ? $couriers->random()->id : null;
                }

                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'customer_id' => $customer->id,
                    'courier_id' => $courierId,
                    'admin_id' => $adminId,
                    'item_description' => $itemDescriptions[array_rand($itemDescriptions)],
                    'item_weight' => $itemWeight,
                    'item_price' => $itemPrice,
                    'service_fee' => $serviceFee,
                    'shipping_cost' => $shippingCost,
                    'total_amount' => $totalAmount,
                    'shipping_method' => $shippingMethod,
                    'origin_address' => $originAddress->address_line,
                    'destination_address' => $destinationAddress->address_line,
                    'origin_city' => $originAddress->city ? $originAddress->city->rajaongkir_id : null,
                    'destination_city' => $destinationAddress->city ? $destinationAddress->city->rajaongkir_id : null,
                    'courier_service' => $shippingMethod === 'rajaongkir' ? $this->getRandomCourierService() : null,
                    'tracking_number' => $shippingMethod === 'rajaongkir' ? $this->generateTrackingNumber() : null,
                    'status' => $status,
                    'estimated_delivery' => $this->getEstimatedDelivery($status),
                    'created_at' => $this->getRandomDate(),
                    'updated_at' => $this->getRandomDate(),
                ]);

                // Create order status history
                $this->createOrderStatusHistory($order, $status, $adminId);
            }
        }

        $this->command->info('OrderSeeder completed successfully!');
    }

    /**
     * Calculate shipping cost based on weight and method
     */
    private function calculateShippingCost(float $weight, string $method): float
    {
        if ($method === 'manual') {
            if ($weight <= 1) return 15000;
            if ($weight <= 5) return 25000;
            if ($weight <= 10) return 40000;
            return 40000 + (ceil($weight - 10) * 3000);
        } else {
            // RajaOngkir pricing
            if ($weight <= 1) return 20000;
            if ($weight <= 5) return 35000;
            if ($weight <= 10) return 50000;
            return 50000 + (ceil($weight - 10) * 4000);
        }
    }

    /**
     * Get random courier service for RajaOngkir
     */
    private function getRandomCourierService(): string
    {
        $services = ['jne', 'pos', 'tiki', 'sicepat', 'jnt', 'wahana', 'ninja', 'lion'];
        return $services[array_rand($services)];
    }

    /**
     * Generate tracking number
     */
    private function generateTrackingNumber(): string
    {
        $prefix = strtoupper(substr(md5(uniqid()), 0, 3));
        $numbers = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        return $prefix . $numbers;
    }

    /**
     * Get estimated delivery date based on status
     */
    private function getEstimatedDelivery(string $status): ?string
    {
        if (in_array($status, ['pending', 'confirmed'])) {
            return null;
        }

        $days = rand(1, 7);
        return now()->addDays($days)->toDateTimeString();
    }

    /**
     * Get random date within last 30 days
     */
    private function getRandomDate(): string
    {
        $daysAgo = rand(0, 30);
        return now()->subDays($daysAgo)->toDateTimeString();
    }

    /**
     * Create order status history
     */
    private function createOrderStatusHistory(Order $order, string $currentStatus, ?int $adminId): void
    {
        $statuses = ['pending', 'confirmed', 'assigned', 'picked_up', 'in_transit', 'delivered'];
        $currentIndex = array_search($currentStatus, $statuses);

        for ($i = 0; $i <= $currentIndex; $i++) {
            $status = $statuses[$i];
            $createdAt = $order->created_at->addHours($i * 2);

            OrderStatus::create([
                'order_id' => $order->id,
                'status' => $status,
                'notes' => $this->getStatusNotes($status),
                'updated_by' => $adminId ?? $order->customer_id,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    /**
     * Get status notes
     */
    private function getStatusNotes(string $status): string
    {
        $notes = [
            'pending' => 'Pesanan telah dibuat dan menunggu konfirmasi',
            'confirmed' => 'Pesanan telah dikonfirmasi oleh admin',
            'assigned' => 'Kurir telah ditugaskan untuk pengiriman',
            'picked_up' => 'Barang telah diambil oleh kurir',
            'in_transit' => 'Barang sedang dalam perjalanan',
            'delivered' => 'Barang telah diterima oleh penerima'
        ];

        return $notes[$status] ?? 'Status diperbarui';
    }
}
