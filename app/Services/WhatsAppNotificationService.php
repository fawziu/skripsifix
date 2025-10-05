<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;

class WhatsAppNotificationService
{
    /**
     * Generate WhatsApp notification link for order status update
     */
    public function generateStatusNotificationLink(Order $order, string $status, User $user): ?string
    {
        $customer = $order->customer;

        if (!$customer || !$customer->phone) {
            return null;
        }

        $phone = $this->formatPhoneNumber($customer->phone);
        $message = $this->generateStatusMessage($order, $status, $user);

        return "https://wa.me/{$phone}?text=" . urlencode($message);
    }

    /**
     * Generate WhatsApp notification link for courier assignment
     */
    public function generateCourierAssignmentLink(Order $order, User $courier): ?string
    {
        $customer = $order->customer;

        if (!$customer || !$customer->phone) {
            return null;
        }

        $phone = $this->formatPhoneNumber($customer->phone);
        $message = $this->generateCourierAssignmentMessage($order, $courier);

        return "https://wa.me/{$phone}?text=" . urlencode($message);
    }

    /**
     * Generate WhatsApp notification link for order confirmation
     */
    public function generateOrderConfirmationLink(Order $order, User $admin): ?string
    {
        $customer = $order->customer;

        if (!$customer || !$customer->phone) {
            return null;
        }

        $phone = $this->formatPhoneNumber($customer->phone);
        $message = $this->generateOrderConfirmationMessage($order, $admin);

        return "https://wa.me/{$phone}?text=" . urlencode($message);
    }

    /**
     * Generate WhatsApp notification link for delivery completion
     */
    public function generateDeliveryCompletionLink(Order $order, User $courier): ?string
    {
        $customer = $order->customer;

        if (!$customer || !$customer->phone) {
            return null;
        }

        $phone = $this->formatPhoneNumber($customer->phone);
        $message = $this->generateDeliveryCompletionMessage($order, $courier);

        return "https://wa.me/{$phone}?text=" . urlencode($message);
    }

    /**
     * Format phone number for WhatsApp
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // If doesn't start with 62, add it
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Generate status update message
     */
    private function generateStatusMessage(Order $order, string $status, User $user): string
    {
        $statusText = $this->getStatusText($status);
        $userName = $user->name;
        $orderNumber = $order->order_number;
        $itemDescription = $order->item_description;

        $message = "🔔 *UPDATE STATUS PENGIRIMAN*\n\n";
        $message .= "Halo {$order->customer->name},\n\n";
        $message .= "Status pesanan Anda telah diperbarui:\n\n";
        $message .= "📦 *No. Pesanan:* {$orderNumber}\n";
        $message .= "📝 *Deskripsi:* {$itemDescription}\n";
        $message .= "🔄 *Status Baru:* {$statusText}\n";
        $message .= "👤 *Diperbarui oleh:* {$userName}\n";
        $message .= "⏰ *Waktu:* " . now()->format('d/m/Y H:i') . "\n\n";

        if ($order->tracking_number) {
            $message .= "📋 *No. Resi:* {$order->tracking_number}\n";
        }

        $message .= "\nTerima kasih telah menggunakan layanan kami!\n";
        $message .= "Untuk informasi lebih lanjut, silakan hubungi kami.";

        return $message;
    }

    /**
     * Generate courier assignment message
     */
    private function generateCourierAssignmentMessage(Order $order, User $courier): string
    {
        $orderNumber = $order->order_number;
        $itemDescription = $order->item_description;
        $courierName = $courier->name;
        $courierPhone = $courier->phone;

        $message = "🚚 *KURIR TELAH DITUGASKAN*\n\n";
        $message .= "Halo {$order->customer->name},\n\n";
        $message .= "Kurir telah ditugaskan untuk pesanan Anda:\n\n";
        $message .= "📦 *No. Pesanan:* {$orderNumber}\n";
        $message .= "📝 *Deskripsi:* {$itemDescription}\n";
        $message .= "🚚 *Kurir:* {$courierName}\n";
        $message .= "📞 *Kontak Kurir:* {$courierPhone}\n";
        $message .= "⏰ *Waktu:* " . now()->format('d/m/Y H:i') . "\n\n";

        $message .= "Kurir akan segera menghubungi Anda untuk koordinasi pengiriman.\n\n";
        $message .= "Terima kasih telah menggunakan layanan kami!";

        return $message;
    }

    /**
     * Generate order confirmation message
     */
    private function generateOrderConfirmationMessage(Order $order, User $admin): string
    {
        $orderNumber = $order->order_number;
        $itemDescription = $order->item_description;
        $totalAmount = number_format($order->total_amount, 0, ',', '.');
        $adminName = $admin->name;

        $message = "✅ *PESANAN DIKONFIRMASI*\n\n";
        $message .= "Halo {$order->customer->name},\n\n";
        $message .= "Pesanan Anda telah dikonfirmasi:\n\n";
        $message .= "📦 *No. Pesanan:* {$orderNumber}\n";
        $message .= "📝 *Deskripsi:* {$itemDescription}\n";
        $message .= "💰 *Total Bayar:* Rp {$totalAmount}\n";
        $message .= "👤 *Dikonfirmasi oleh:* {$adminName}\n";
        $message .= "⏰ *Waktu:* " . now()->format('d/m/Y H:i') . "\n\n";

        if ($order->tracking_number) {
            $message .= "📋 *No. Resi:* {$order->tracking_number}\n";
        }

        $message .= "\nPesanan Anda akan segera diproses untuk pengiriman.\n";
        $message .= "Terima kasih telah menggunakan layanan kami!";

        return $message;
    }

    /**
     * Generate delivery completion message
     */
    private function generateDeliveryCompletionMessage(Order $order, User $courier): string
    {
        $orderNumber = $order->order_number;
        $itemDescription = $order->item_description;
        $courierName = $courier->name;

        $message = "🎉 *PESANAN TELAH DITERIMA*\n\n";
        $message .= "Halo {$order->customer->name},\n\n";
        $message .= "Pesanan Anda telah berhasil diterima:\n\n";
        $message .= "📦 *No. Pesanan:* {$orderNumber}\n";
        $message .= "📝 *Deskripsi:* {$itemDescription}\n";
        $message .= "🚚 *Diantar oleh:* {$courierName}\n";
        $message .= "⏰ *Waktu:* " . now()->format('d/m/Y H:i') . "\n\n";

        $message .= "Terima kasih telah menggunakan layanan kami!\n";
        $message .= "Kami berharap Anda puas dengan layanan kami.\n\n";
        $message .= "Silakan berikan ulasan dan rating untuk layanan kami.";

        return $message;
    }

    /**
     * Get status text in Indonesian
     */
    private function getStatusText(string $status): string
    {
        $statusMap = [
            'pending' => 'Menunggu Konfirmasi',
            'confirmed' => 'Dikonfirmasi',
            'assigned' => 'Ditugaskan ke Kurir',
            'picked_up' => 'Diambil Kurir',
            'in_transit' => 'Dalam Perjalanan',
            'awaiting_confirmation' => 'Menunggu Konfirmasi Customer',
            'delivered' => 'Telah Diterima',
            'cancelled' => 'Dibatalkan',
            'failed' => 'Gagal Dikirim'
        ];

        return $statusMap[$status] ?? ucfirst($status);
    }

    /**
     * Generate WhatsApp notification link for pickup request
     */
    public function generatePickupRequestLink(Order $order, array $pickupData): ?string
    {
        $customer = $order->customer;

        if (!$customer || !$customer->phone) {
            return null;
        }

        $phone = $this->formatPhoneNumber($customer->phone);
        $message = $this->generatePickupRequestMessage($order, $pickupData);

        return "https://wa.me/{$phone}?text=" . urlencode($message);
    }

    /**
     * Generate pickup request message
     */
    private function generatePickupRequestMessage(Order $order, array $pickupData): string
    {
        $orderNumber = $order->order_number;
        $itemDescription = $order->item_description;
        $pickupDate = $pickupData['pickup_date'];
        $pickupTime = $pickupData['pickup_time'];
        $pickupAddress = $pickupData['pickup_address'];
        $pickupContact = $pickupData['pickup_contact'];
        $pickupPhone = $pickupData['pickup_phone'];

        $message = "🚚 *REQUEST PICKUP*\n\n";
        $message .= "Halo {$order->customer->name},\n\n";
        $message .= "Request pickup untuk pesanan Anda telah dibuat:\n\n";
        $message .= "📦 *No. Pesanan:* {$orderNumber}\n";
        $message .= "📝 *Deskripsi:* {$itemDescription}\n";
        $message .= "📅 *Tanggal Pickup:* {$pickupDate}\n";
        $message .= "⏰ *Waktu Pickup:* {$pickupTime}\n";
        $message .= "📍 *Alamat Pickup:* {$pickupAddress}\n";
        $message .= "👤 *Kontak:* {$pickupContact}\n";
        $message .= "📞 *Telepon:* {$pickupPhone}\n";
        $message .= "⏰ *Waktu Request:* " . now()->format('d/m/Y H:i') . "\n\n";

        if (isset($pickupData['notes']) && !empty($pickupData['notes'])) {
            $message .= "📝 *Catatan:* {$pickupData['notes']}\n\n";
        }

        $message .= "Kurir akan menghubungi Anda untuk konfirmasi pickup.\n";
        $message .= "Terima kasih telah menggunakan layanan kami!";

        return $message;
    }

    /**
     * Generate WhatsApp notification link for delivery confirmation by customer
     */
    public function generateDeliveryConfirmationLink(Order $order, User $customer): ?string
    {
        $courier = $order->courier;

        if (!$courier || !$courier->phone) {
            return null;
        }

        $phone = $this->formatPhoneNumber($courier->phone);
        $message = $this->generateDeliveryConfirmationMessage($order, $customer);

        return "https://wa.me/{$phone}?text=" . urlencode($message);
    }

    /**
     * Generate delivery confirmation message for courier
     */
    private function generateDeliveryConfirmationMessage(Order $order, User $customer): string
    {
        $orderNumber = $order->order_number;
        $itemDescription = $order->item_description;
        $customerName = $customer->name;

        $message = "✅ *KONFIRMASI PENERIMAAN BARANG*\n\n";
        $message .= "Halo Kurir,\n\n";
        $message .= "Customer telah mengonfirmasi penerimaan barang:\n\n";
        $message .= "📦 *No. Pesanan:* {$orderNumber}\n";
        $message .= "📝 *Deskripsi:* {$itemDescription}\n";
        $message .= "👤 *Customer:* {$customerName}\n";
        $message .= "⏰ *Waktu Konfirmasi:* " . now()->format('d/m/Y H:i') . "\n\n";

        $message .= "Pesanan telah berhasil diselesaikan!\n";
        $message .= "Terima kasih atas layanan pengiriman yang baik.";

        return $message;
    }

    /**
     * Generate notification link based on status
     */
    public function generateNotificationLink(Order $order, string $status, User $user): ?string
    {
        switch ($status) {
            case 'confirmed':
                return $this->generateOrderConfirmationLink($order, $user);
            case 'assigned':
                return $this->generateCourierAssignmentLink($order, $user);
            case 'delivered':
                return $this->generateDeliveryCompletionLink($order, $user);
            default:
                return $this->generateStatusNotificationLink($order, $status, $user);
        }
    }
}
