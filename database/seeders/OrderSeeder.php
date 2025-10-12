<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\User;
use App\Models\Address;
use App\Models\OrderStatus;
use App\Models\City;

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

        // Pastikan setiap customer memiliki alamat
        $this->ensureCustomersHaveAddresses($customers);

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
            'Kosmetik dan skincare',
            'Obat-obatan',
            'Peralatan kantor',
            'Bahan makanan',
            'Peralatan masak',
            'Aksesoris kendaraan'
        ];

        $shippingMethods = ['manual', 'rajaongkir'];
        $paymentMethods = ['cod', 'transfer'];
        $statuses = ['pending', 'confirmed', 'assigned', 'picked_up', 'in_transit', 'delivered'];

        $orderCount = 0;
        $manualOrderCount = 0;
        $rajaOngkirOrderCount = 0;

        foreach ($customers as $customer) {
            // Create 4-10 orders per customer dengan distribusi yang seimbang
            $numOrders = rand(4, 10);

            for ($i = 0; $i < $numOrders; $i++) {
                // Pastikan distribusi 50-50 antara manual dan raja ongkir
                if ($i < $numOrders / 2) {
                    $shippingMethod = 'manual';
                    $manualOrderCount++;
                } else {
                    $shippingMethod = 'rajaongkir';
                    $rajaOngkirOrderCount++;
                }

                // Untuk raja ongkir, gunakan transfer sebagai default payment method
                $paymentMethod = $shippingMethod === 'manual' ? $paymentMethods[array_rand($paymentMethods)] : 'transfer';

                // Get customer addresses
                $customerAddresses = $customer->addresses()->active()->get();
                if ($customerAddresses->isEmpty()) {
                    // Buat alamat default jika tidak ada
                    $this->createDefaultAddress($customer);
                    $customerAddresses = $customer->addresses()->active()->get();
                }

                $originAddress = $customerAddresses->random();
                $destinationAddress = $customerAddresses->random();

                // Ensure different addresses for origin and destination
                while ($originAddress->id === $destinationAddress->id && $customerAddresses->count() > 1) {
                    $destinationAddress = $customerAddresses->random();
                }

                // Generate order data yang lebih realistis
                $itemWeight = $this->getRealisticWeight();
                $itemPrice = $this->getRealisticPrice();
                $shippingCost = $this->calculateShippingCost($itemWeight, $shippingMethod);
                $serviceFee = $this->getServiceFee($shippingMethod);
                $totalAmount = $itemPrice + $shippingCost + $serviceFee;

                // Determine status and assign courier/admin accordingly
                $status = $this->getRealisticStatus();
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
                    'order_number' => $this->generateOrderNumber(),
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
                    'payment_method' => $paymentMethod,
                    'origin_address' => $originAddress->address_line,
                    'destination_address' => $destinationAddress->address_line,
                    'origin_city' => $originAddress->city ? $originAddress->city->rajaongkir_id : null,
                    'destination_city' => $destinationAddress->city ? $destinationAddress->city->rajaongkir_id : null,
                    'courier_service' => $shippingMethod === 'rajaongkir' ? $this->getRandomCourierService() : null,
                    'tracking_number' => $shippingMethod === 'rajaongkir' ? $this->generateTrackingNumber() : null,
                    'status' => $status,
                    'estimated_delivery' => $this->getEstimatedDelivery($status),
                    'rajaongkir_response' => $shippingMethod === 'rajaongkir' ? $this->getRajaOngkirResponse() : null,
                    'created_at' => $this->getRandomDate(),
                    'updated_at' => $this->getRandomDate(),
                ]);

                // Create order status history
                $this->createOrderStatusHistory($order, $status, $adminId);
                $orderCount++;
            }
        }

        $this->command->info("OrderSeeder completed successfully!");
        $this->command->info("Total orders created: {$orderCount}");
        $this->command->info("Manual orders: {$manualOrderCount}");
        $this->command->info("RajaOngkir orders: {$rajaOngkirOrderCount}");
    }

    /**
     * Pastikan setiap customer memiliki alamat
     */
    private function ensureCustomersHaveAddresses($customers): void
    {
        foreach ($customers as $customer) {
            $addressCount = $customer->addresses()->active()->count();
            if ($addressCount === 0) {
                $this->createDefaultAddress($customer);
            }
        }
    }

    /**
     * Buat alamat default untuk customer
     */
    private function createDefaultAddress(User $customer): void
    {
        // Ambil kota pertama yang tersedia
        $city = City::first();
        if (!$city) {
            return;
        }

        // Generate default coordinates (Makassar)
        $coordinates = [
            'latitude' => -5.135399 + (rand(-10000, 10000) / 100000),
            'longitude' => 119.423790 + (rand(-10000, 10000) / 100000),
            'accuracy' => rand(5, 50)
        ];

        Address::create([
            'user_id' => $customer->id,
            'type' => 'home',
            'label' => 'Alamat Utama',
            'recipient_name' => $customer->name,
            'phone' => $customer->phone,
            'province_id' => $city->province_id,
            'city_id' => $city->id,
            'district_id' => null,
            'postal_code' => '90000',
            'address_line' => $customer->address ?? 'Jl. Default No. 1',
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
            'accuracy' => $coordinates['accuracy'],
            'is_primary' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Generate order number
     */
    private function generateOrderNumber(): string
    {
        do {
            $prefix = 'ORD';
            $date = now()->format('Ymd');
            $random = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $microtime = substr(str_replace('.', '', microtime(true)), -3);
            $orderNumber = $prefix . $date . $random . $microtime;
            
            // Check if order number already exists
            $exists = \App\Models\Order::where('order_number', $orderNumber)->exists();
        } while ($exists);
        
        return $orderNumber;
    }

    /**
     * Get realistic weight distribution
     */
    private function getRealisticWeight(): float
    {
        $weights = [0.5, 0.8, 1.0, 1.2, 1.5, 2.0, 2.5, 3.0, 4.0, 5.0, 7.5, 10.0];
        return $weights[array_rand($weights)];
    }

    /**
     * Get realistic price distribution
     */
    private function getRealisticPrice(): float
    {
        $priceRanges = [
            [50000, 150000],    // 50k-150k (30%)
            [150000, 500000],   // 150k-500k (40%)
            [500000, 1000000],  // 500k-1M (20%)
            [1000000, 2000000]  // 1M-2M (10%)
        ];
        
        $range = $priceRanges[array_rand($priceRanges)];
        return rand($range[0], $range[1]);
    }

    /**
     * Get service fee based on shipping method
     */
    private function getServiceFee(string $method): float
    {
        if ($method === 'manual') {
            return rand(3000, 8000);
        } else {
            return rand(2000, 5000);
        }
    }

    /**
     * Get realistic status distribution
     */
    private function getRealisticStatus(): string
    {
        $statuses = [
            'pending' => 15,
            'confirmed' => 20,
            'assigned' => 15,
            'picked_up' => 15,
            'in_transit' => 20,
            'delivered' => 15
        ];

        $rand = rand(1, 100);
        $cumulative = 0;
        
        foreach ($statuses as $status => $percentage) {
            $cumulative += $percentage;
            if ($rand <= $cumulative) {
                return $status;
            }
        }
        
        return 'pending';
    }

    /**
     * Get RajaOngkir response data
     */
    private function getRajaOngkirResponse(): array
    {
        $services = ['jne', 'pos', 'tiki', 'sicepat', 'jnt', 'wahana', 'ninja', 'lion'];
        $service = $services[array_rand($services)];
        
        return [
            'service' => $service,
            'service_name' => strtoupper($service),
            'cost' => rand(15000, 50000),
            'etd' => rand(1, 7) . ' HARI',
            'note' => 'Estimasi pengiriman ' . rand(1, 7) . ' hari kerja'
        ];
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
