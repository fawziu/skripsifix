<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $botToken;
    private string $apiBaseUrl;

    public function __construct()
    {
        $this->botToken = (string) config('services.telegram.bot_token');
        $this->apiBaseUrl = 'https://api.telegram.org/bot' . $this->botToken;
    }

    public function sendMessage(int|string $chatId, string $text, array $options = []): bool
    {
        if (empty($this->botToken) || empty($chatId) || empty($text)) {
            return false;
        }

        $payload = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => true,
        ], $options);

        try {
            $response = Http::timeout(10)->post($this->apiBaseUrl . '/sendMessage', $payload);
            if (!$response->successful()) {
                Log::warning('Telegram sendMessage failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }
            return (bool) ($response->json('ok') === true);
        } catch (\Throwable $e) {
            Log::error('Telegram sendMessage exception', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function sendOrderStatusUpdate(Order $order, string $status, User $actor): bool
    {
        $customer = $order->customer;
        if (!$customer || empty($customer->telegram_chat_id)) {
            return false;
        }

        $message = $this->buildStatusMessage($order, $status, $actor);
        return $this->sendMessage($customer->telegram_chat_id, $message);
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
