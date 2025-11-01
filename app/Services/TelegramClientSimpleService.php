<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Telegram Client Simple Service - Alternative tanpa MadelineProto
 * 
 * Pendekatan ini menggunakan Telegram Client API via HTTP langsung
 * Untuk testing sederhana tanpa perlu library berat
 * 
 * Catatan: Masih perlu api_id, api_hash, dan session management
 */
class TelegramClientSimpleService
{
    private ?string $apiId;
    private ?string $apiHash;
    private ?string $phoneNumber;
    private string $sessionPath;

    public function __construct()
    {
        $this->apiId = config('services.telegram.client_api_id');
        $this->apiHash = config('services.telegram.client_api_hash');
        $this->phoneNumber = config('services.telegram.client_phone');
        $this->sessionPath = storage_path('app/telegram_session');
        
        if (!file_exists($this->sessionPath)) {
            mkdir($this->sessionPath, 0755, true);
        }
    }

    /**
     * Send message via simple HTTP approach
     * 
     * Untuk testing: Gunakan ini jika MadelineProto tidak bisa diinstall
     * Tapi catatan: Implementasi ini masih terbatas dan butuh session yang valid
     */
    public function sendMessage(string $target, string $message): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('Telegram Client Simple not configured');
            return false;
        }

        // Untuk implementasi sederhana, kita bisa gunakan pendekatan lain
        // atau fallback ke Bot API
        Log::info('Telegram Client Simple: Fallback to Bot API recommended for testing');
        return false;
    }

    /**
     * Send order status update
     */
    public function sendOrderStatusUpdate(Order $order, string $status, User $actor): bool
    {
        $customer = $order->customer;
        if (!$customer || !$customer->phone) {
            return false;
        }

        $message = $this->buildStatusMessage($order, $status, $actor);
        return $this->sendMessage($customer->phone, $message);
    }

    /**
     * Check if service is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiId) && !empty($this->apiHash) && !empty($this->phoneNumber);
    }

    private function buildStatusMessage(Order $order, string $status, User $actor): string
    {
        $statusText = $this->getStatusText($status);
        $orderNumber = $order->order_number;
        $itemDescription = $order->item_description;

        $message = "📦 *Status Pesanan Diperbarui*\n\n";
        $message .= "Halo {$order->customer->name},\n\n";
        $message .= "No. Pesanan: *{$orderNumber}*\n";
        $message .= "Deskripsi: {$itemDescription}\n";
        $message .= "Status: *{$statusText}*\n";
        if (!empty($order->tracking_number)) {
            $message .= "Resi: `{$order->tracking_number}`\n";
        }
        $message .= "Diupdate oleh: {$actor->name}\n";
        $message .= "Waktu: " . now()->format('d/m/Y H:i') . "\n\n";
        $message .= "Terima kasih telah menggunakan layanan kami.";

        return $message;
    }

    private function getStatusText(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu Konfirmasi',
            'confirmed' => 'Dikonfirmasi',
            'assigned' => 'Kurir Ditugaskan',
            'picked_up' => 'Sudah Diambil Kurir',
            'in_transit' => 'Dalam Perjalanan',
            'awaiting_confirmation' => 'Menunggu Konfirmasi Penerima',
            'delivered' => 'Terkirim',
            'cancelled' => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}

