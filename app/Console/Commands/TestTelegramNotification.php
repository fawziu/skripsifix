<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\User;
use App\Services\TelegramService;
use App\Services\TelegramClientService;
use Illuminate\Console\Command;

class TestTelegramNotification extends Command
{
    protected $signature = 'telegram:test {--order=} {--status=} {--chat-id=} {--phone=} {--override-phone=}';
    protected $description = 'Test Telegram notification (works without webhook)';

    public function handle(TelegramService $telegramService, TelegramClientService $telegramClientService = null)
    {
        // Option 1: Test with order ID
        if ($orderId = $this->option('order')) {
            $order = Order::with('customer')->find($orderId);
            if (!$order) {
                $this->error("Order #{$orderId} not found!");
                return 1;
            }

            $customer = $order->customer;
            $status = $this->option('status') ?? $order->status;
            $actor = $order->admin ?? $order->courier ?? $customer;

            // Override phone number if specified
            $targetPhone = $this->option('override-phone') ?? $customer->phone;
            if ($this->option('override-phone')) {
                $this->info("📱 Menggunakan nomor override: {$targetPhone} (asal: {$customer->phone})");
                // Temporarily override customer phone for testing
                $customer->phone = $targetPhone;
            }

            // Send via Client API only
            if (!$telegramClientService || !$telegramClientService->isConfigured()) {
                $this->error("❌ Telegram Client API belum dikonfigurasi!");
                $this->line("Tambahkan ke .env:");
                $this->line("  TELEGRAM_CLIENT_API_ID=your_api_id");
                $this->line("  TELEGRAM_CLIENT_API_HASH=your_api_hash");
                $this->line("  TELEGRAM_CLIENT_PHONE=your_phone");
                return 1;
            }

            $this->info("📤 Mengirim via Telegram Client API ke nomor: {$targetPhone}");
            
            try {
                $result = $telegramClientService->sendOrderStatusUpdate($order, $status, $actor);
                
                if ($result) {
                    $this->info("✅ Pesan berhasil dikirim via Client API!");
                    return 0;
                } else {
                    $this->error("❌ Gagal mengirim pesan. Cek logs untuk detail.");
                    return 1;
                }
            } catch (\Throwable $e) {
                $this->error("❌ Error: " . $e->getMessage());
                $this->newLine();
                $this->line("💡 Solusi:");
                $this->line("   1. Pastikan nomor {$targetPhone} sudah ada di kontak Telegram akun Anda");
                $this->line("   2. Atau user dengan nomor tersebut sudah pernah chat dengan akun Telegram Anda");
                $this->line("   3. Atau gunakan username Telegram (@username) jika tersedia");
                return 1;
            }
        }

        // Option 2: Test with chat_id directly
        if ($chatId = $this->option('chat-id')) {
            $message = $this->ask('Masukkan pesan test:', 'Test pesan dari bot');
            $result = $telegramService->sendMessage($chatId, $message);

            if ($result) {
                $this->info("✅ Pesan berhasil dikirim ke chat_id: {$chatId}");
            } else {
                $this->error("❌ Gagal mengirim pesan.");
            }

            return $result ? 0 : 1;
        }

        // Option 3: Test with phone number directly (Client API)
        if ($phone = $this->option('phone')) {
            if (!$telegramClientService || !$telegramClientService->isConfigured()) {
                $this->error("❌ Telegram Client API belum dikonfigurasi!");
                $this->line("Tambahkan ke .env:");
                $this->line("  TELEGRAM_CLIENT_API_ID=your_api_id");
                $this->line("  TELEGRAM_CLIENT_API_HASH=your_api_hash");
                $this->line("  TELEGRAM_CLIENT_PHONE=your_phone");
                return 1;
            }

            $message = $this->ask('Masukkan pesan test:', 'Test pesan dari Telegram Client');
            $result = $telegramClientService->sendMessage($phone, $message);

            if ($result) {
                $this->info("✅ Pesan berhasil dikirim ke {$phone} via Client API!");
            } else {
                $this->error("❌ Gagal mengirim pesan.");
            }

            return $result ? 0 : 1;
        }

        $this->error("Gunakan salah satu opsi:");
        $this->line("  php artisan telegram:test --order=1 --status=delivered");
        $this->line("  php artisan telegram:test --order=1 --override-phone=087866707600");
        $this->line("  php artisan telegram:test --phone=087866707600");
        $this->line("  php artisan telegram:test --chat-id=123456789");

        return 1;
    }
}

