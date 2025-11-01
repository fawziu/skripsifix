<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Command untuk autentikasi Telegram Client API
 * 
 * Untuk mendapatkan api_id dan api_hash:
 * 1. Buka https://my.telegram.org/apps
 * 2. Login dengan nomor telepon Anda
 * 3. Buat aplikasi baru (atau gunakan yang sudah ada)
 * 4. Copy api_id dan api_hash ke .env
 */
class TelegramClientAuth extends Command
{
    protected $signature = 'telegram:client-auth';
    protected $description = 'Authenticate Telegram Client API (one-time setup)';

    public function handle()
    {
        $this->info('🔐 Telegram Client API Authentication');
        $this->newLine();

        // Check if MadelineProto is available
        if (!class_exists('\danog\MadelineProto\API')) {
            $this->error('❌ MadelineProto library tidak terinstall!');
            $this->newLine();
            $this->line('Install dengan:');
            $this->line('  composer require danog/madelineproto');
            $this->newLine();
            $this->line('Atau gunakan pendekatan sederhana (lihat dokumentasi).');
            return 1;
        }

        $apiId = config('services.telegram.client_api_id');
        $apiHash = config('services.telegram.client_api_hash');
        $phone = config('services.telegram.client_phone');

        if (empty($apiId) || empty($apiHash) || empty($phone)) {
            $this->error('❌ Konfigurasi belum lengkap!');
            $this->newLine();
            $this->line('Tambahkan ke .env:');
            $this->line('  TELEGRAM_CLIENT_API_ID=your_api_id');
            $this->line('  TELEGRAM_CLIENT_API_HASH=your_api_hash');
            $this->line('  TELEGRAM_CLIENT_PHONE=your_phone_number');
            $this->newLine();
            $this->line('Dapatkan dari: https://my.telegram.org/apps');
            return 1;
        }

        $this->info("✅ Config ditemukan:");
        $this->line("   API ID: {$apiId}");
        $this->line("   Phone: {$phone}");
        $this->newLine();

        try {
            $sessionPath = storage_path('app/telegram_session/session.madeline');
            
            // Ensure directory exists
            $sessionDir = dirname($sessionPath);
            if (!file_exists($sessionDir)) {
                mkdir($sessionDir, 0755, true);
            }
            
            $this->info('📱 Membuat session...');
            
            // Create settings object with Windows compatibility
            // @phpstan-ignore-next-line - MadelineProto loaded conditionally
            $appInfo = (new \danog\MadelineProto\Settings\AppInfo)
                ->setApiId((int) $apiId)
                ->setApiHash($apiHash);
            
            // Use file-based serialization for Windows (disable IPC)
            // @phpstan-ignore-next-line - MadelineProto loaded conditionally
            $serialization = new \danog\MadelineProto\Settings\Serialization;
            $serialization->setSerializationType(\danog\MadelineProto\Settings\Serialization::FILE);
            
            // @phpstan-ignore-next-line - MadelineProto loaded conditionally
            $allSettings = new \danog\MadelineProto\Settings;
            $allSettings->setAppInfo($appInfo);
            $allSettings->setSerialization($serialization);
            
            // @phpstan-ignore-next-line - MadelineProto loaded conditionally
            $api = new \danog\MadelineProto\API($sessionPath, $allSettings);

            $this->info('📲 Mengirim kode verifikasi...');
            
            $sentCode = $api->phoneLogin($phone);
            
            $this->info('✅ Kode verifikasi dikirim ke Telegram Anda!');
            $this->newLine();
            
            $code = $this->ask('Masukkan kode verifikasi yang diterima:');
            
            $this->info('🔐 Memverifikasi kode...');
            
            try {
                $api->completePhoneLogin($code);
                $this->info('✅ Login berhasil!');
            } catch (\danog\MadelineProto\RPCErrorException $e) {
                if ($e->getMessage() === 'SESSION_PASSWORD_NEEDED') {
                    $this->info('🔒 Akun menggunakan 2FA. Masukkan password:');
                    $password = $this->secret('Password 2FA:');
                    $api->complete2faLogin($password);
                    $this->info('✅ Login 2FA berhasil!');
                } else {
                    throw $e;
                }
            }

            $this->newLine();
            $this->info('🎉 Session berhasil dibuat!');
            $this->line('   Session file: ' . $sessionPath);
            $this->newLine();
            $this->line('Sekarang Anda bisa menggunakan Telegram Client Service.');
            
            return 0;
        } catch (\Throwable $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}

