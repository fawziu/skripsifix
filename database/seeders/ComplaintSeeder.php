<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Order;

class ComplaintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users and orders
        $customers = User::whereHas('role', function($query) {
            $query->where('name', 'customer');
        })->get();

        $orders = Order::whereIn('status', ['assigned', 'picked_up', 'in_transit', 'delivered'])->get();

        if ($customers->isEmpty() || $orders->isEmpty()) {
            $this->command->warn('Skipping ComplaintSeeder: No customers or orders found.');
            return;
        }

        $complaintTypes = [
            'delivery_delay' => 'Keterlambatan pengiriman',
            'damaged_package' => 'Paket rusak',
            'wrong_item' => 'Barang tidak sesuai',
            'lost_package' => 'Paket hilang',
            'poor_service' => 'Pelayanan kurang baik',
            'billing_issue' => 'Masalah tagihan',
            'tracking_issue' => 'Masalah tracking',
            'courier_behavior' => 'Perilaku kurir',
            'other' => 'Lainnya'
        ];

        $priorities = ['low', 'medium', 'high', 'urgent'];
        $statuses = ['open', 'in_progress', 'resolved', 'closed'];

        foreach ($customers as $customer) {
            // Create 1-3 complaints per customer
            $numComplaints = rand(1, 3);

            for ($i = 0; $i < $numComplaints; $i++) {
                // Get random order for this customer
                $customerOrders = $orders->where('customer_id', $customer->id);
                if ($customerOrders->isEmpty()) {
                    continue;
                }

                $order = $customerOrders->random();
                $complaintType = array_rand($complaintTypes);
                $priority = $priorities[array_rand($priorities)];
                $status = $statuses[array_rand($statuses)];

                // Generate complaint data
                $subject = $this->generateComplaintSubject($complaintType, $order);
                $description = $this->generateComplaintDescription($complaintType, $order);
                $resolution = $status === 'resolved' ? $this->generateResolution($complaintType) : null;
                $resolvedAt = $status === 'resolved' ? $this->getRandomResolvedDate() : null;

                Complaint::create([
                    'user_id' => $customer->id,
                    'order_id' => $order->id,
                    'title' => $subject,
                    'description' => $description,
                    'type' => $this->mapComplaintType($complaintType),
                    'priority' => $priority,
                    'status' => $status,
                    'assigned_to' => null, // Will be assigned by admin later
                    'resolution' => $resolution,
                    'resolved_at' => $resolvedAt,
                    'created_at' => $this->getRandomDate($order->created_at),
                    'updated_at' => $this->getRandomDate($order->created_at),
                ]);
            }
        }

        $this->command->info('ComplaintSeeder completed successfully!');
    }

    /**
     * Map complaint type to match database enum values
     */
    private function mapComplaintType(string $type): string
    {
        $typeMapping = [
            'delivery_delay' => 'delivery',
            'damaged_package' => 'delivery',
            'wrong_item' => 'delivery',
            'lost_package' => 'delivery',
            'poor_service' => 'service',
            'courier_behavior' => 'service',
            'billing_issue' => 'payment',
            'tracking_issue' => 'service',
            'other' => 'other'
        ];

        return $typeMapping[$type] ?? 'other';
    }

    /**
     * Generate complaint subject based on type
     */
    private function generateComplaintSubject(string $type, Order $order): string
    {
        $subjects = [
            'delivery_delay' => [
                'Keterlambatan pengiriman pesanan #' . $order->order_number,
                'Paket belum sampai sesuai estimasi',
                'Pengiriman terlambat dari jadwal'
            ],
            'damaged_package' => [
                'Paket rusak saat diterima',
                'Barang cacat dalam pengiriman',
                'Kondisi paket tidak baik'
            ],
            'wrong_item' => [
                'Barang yang diterima tidak sesuai',
                'Pesanan salah kirim',
                'Barang tidak sesuai deskripsi'
            ],
            'lost_package' => [
                'Paket hilang dalam pengiriman',
                'Barang tidak ditemukan',
                'Status paket tidak jelas'
            ],
            'poor_service' => [
                'Pelayanan kurang memuaskan',
                'Kurir tidak profesional',
                'Respon lambat dari customer service'
            ],
            'billing_issue' => [
                'Masalah dengan tagihan',
                'Biaya tidak sesuai perhitungan',
                'Kesalahan dalam penagihan'
            ],
            'tracking_issue' => [
                'Informasi tracking tidak akurat',
                'Status pengiriman tidak update',
                'Kesulitan melacak paket'
            ],
            'courier_behavior' => [
                'Perilaku kurir tidak sopan',
                'Kurir tidak mengikuti protokol',
                'Sikap kurir kurang baik'
            ],
            'other' => [
                'Keluhan lainnya terkait pesanan',
                'Masalah tidak terduga',
                'Permintaan informasi tambahan'
            ]
        ];

        $typeSubjects = $subjects[$type] ?? $subjects['other'];
        return $typeSubjects[array_rand($typeSubjects)];
    }

    /**
     * Generate complaint description based on type
     */
    private function generateComplaintDescription(string $type, Order $order): string
    {
        $descriptions = [
            'delivery_delay' => "Pesanan dengan nomor {$order->order_number} seharusnya sudah sampai pada " .
                               $order->estimated_delivery . " namun hingga saat ini belum diterima. " .
                               "Mohon informasi lebih lanjut mengenai status pengiriman dan estimasi waktu kedatangan yang baru.",

            'damaged_package' => "Paket pesanan {$order->order_number} telah diterima namun dalam kondisi rusak. " .
                                "Barang yang dipesan: {$order->item_description}. " .
                                "Kondisi kerusakan: kemasan robek dan barang sedikit tergores. " .
                                "Mohon penanganan dan kompensasi yang sesuai.",

            'wrong_item' => "Pesanan {$order->order_number} telah diterima namun barang yang dikirim tidak sesuai. " .
                           "Barang yang dipesan: {$order->item_description}. " .
                           "Barang yang diterima: barang lain yang tidak sesuai pesanan. " .
                           "Mohon penggantian dengan barang yang benar.",

            'lost_package' => "Pesanan {$order->order_number} belum diterima dan status tracking menunjukkan 'dalam pengiriman' " .
                             "sejak beberapa hari yang lalu. Mohon investigasi dan informasi status terkini.",

            'poor_service' => "Pelayanan yang diterima untuk pesanan {$order->order_number} kurang memuaskan. " .
                             "Kurir tidak memberikan informasi yang jelas dan respon lambat. " .
                             "Mohon perbaikan pelayanan ke depannya.",

            'billing_issue' => "Ada kesalahan dalam perhitungan biaya untuk pesanan {$order->order_number}. " .
                              "Total yang dibayar: Rp " . number_format($order->total_amount, 0, ',', '.') . ". " .
                              "Mohon verifikasi dan koreksi jika ada kesalahan.",

            'tracking_issue' => "Informasi tracking untuk pesanan {$order->order_number} tidak akurat dan tidak update. " .
                               "Status tetap 'dalam pengiriman' padahal seharusnya sudah sampai. " .
                               "Mohon update informasi tracking yang benar.",

            'courier_behavior' => "Kurir yang mengantar pesanan {$order->order_number} kurang sopan dan tidak mengikuti protokol. " .
                                 "Tidak memberikan salam dan langsung meninggalkan paket. " .
                                 "Mohon pembinaan untuk kurir tersebut.",

            'other' => "Keluhan terkait pesanan {$order->order_number}: {$order->item_description}. " .
                       "Mohon penanganan dan informasi lebih lanjut."
        ];

        return $descriptions[$type] ?? $descriptions['other'];
    }

    /**
     * Generate resolution for resolved complaints
     */
    private function generateResolution(string $type): string
    {
        $resolutions = [
            'delivery_delay' => [
                'Paket telah berhasil dikirim ulang dan diterima oleh customer. Permintaan maaf telah disampaikan dan kompensasi ongkir diberikan.',
                'Kurir telah diarahkan untuk prioritas pengiriman. Customer puas dengan penanganan yang diberikan.',
                'Masalah keterlambatan telah diselesaikan dengan pengiriman ekstra cepat. Customer menerima kompensasi.'
            ],
            'damaged_package' => [
                'Barang telah diganti dengan yang baru dan dikirim ulang. Customer puas dengan penanganan.',
                'Kompensasi telah diberikan sesuai kerusakan. Customer menerima penggantian barang.',
                'Masalah kerusakan telah diselesaikan dengan penggantian dan permintaan maaf resmi.'
            ],
            'wrong_item' => [
                'Barang yang benar telah dikirim ulang dan barang salah telah diambil kembali. Customer puas.',
                'Penggantian barang telah dilakukan dengan prioritas tinggi. Permintaan maaf disampaikan.',
                'Masalah barang salah telah diselesaikan dengan penggantian dan kompensasi ongkir.'
            ],
            'lost_package' => [
                'Paket telah ditemukan dan dikirim ulang ke customer. Permintaan maaf dan kompensasi diberikan.',
                'Investigasi telah selesai dan paket berhasil dikirim. Customer menerima kompensasi penuh.',
                'Masalah paket hilang telah diselesaikan dengan pengiriman ulang dan kompensasi.'
            ],
            'poor_service' => [
                'Keluhan telah disampaikan ke tim kurir dan pelatihan tambahan diberikan. Customer puas dengan tindak lanjut.',
                'Permintaan maaf resmi telah disampaikan dan perbaikan pelayanan dijanjikan.',
                'Masalah pelayanan telah ditangani dengan pembinaan kurir dan kompensasi.'
            ],
            'billing_issue' => [
                'Verifikasi billing telah selesai dan koreksi dilakukan. Customer puas dengan penyelesaian.',
                'Kesalahan billing telah diperbaiki dan kompensasi diberikan. Permintaan maaf disampaikan.',
                'Masalah billing telah diselesaikan dengan koreksi dan kompensasi yang sesuai.'
            ],
            'tracking_issue' => [
                'Informasi tracking telah diperbaiki dan update secara real-time. Customer puas dengan perbaikan.',
                'Sistem tracking telah diperbaiki dan informasi akurat diberikan. Permintaan maaf disampaikan.',
                'Masalah tracking telah diselesaikan dengan perbaikan sistem dan kompensasi.'
            ],
            'courier_behavior' => [
                'Kurir telah diberi pembinaan dan permintaan maaf disampaikan. Customer puas dengan tindak lanjut.',
                'Keluhan telah disampaikan ke tim HR dan pembinaan diberikan. Kompensasi diberikan.',
                'Masalah perilaku kurir telah ditangani dengan pembinaan dan kompensasi.'
            ],
            'other' => [
                'Keluhan telah ditangani sesuai dengan jenis masalah. Customer puas dengan penyelesaian.',
                'Masalah telah diselesaikan dengan penanganan yang tepat. Permintaan maaf disampaikan.',
                'Keluhan telah ditangani dan penyelesaian yang memuaskan diberikan.'
            ]
        ];

        $typeResolutions = $resolutions[$type] ?? $resolutions['other'];
        return $typeResolutions[array_rand($typeResolutions)];
    }

    /**
     * Get random date for complaint creation
     */
    private function getRandomDate($orderCreatedAt): string
    {
        $daysAfterOrder = rand(1, 14); // Complaint created 1-14 days after order
        return $orderCreatedAt->addDays($daysAfterOrder)->toDateTimeString();
    }

    /**
     * Get random resolved date
     */
    private function getRandomResolvedDate(): string
    {
        $daysAgo = rand(1, 30);
        return now()->subDays($daysAgo)->toDateTimeString();
    }
}
