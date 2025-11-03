<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Telegram Client Service - Mengirim pesan dari akun Telegram pribadi
 * 
 * Catatan: Ini memerlukan api_id dan api_hash dari https://my.telegram.org/apps
 * Session akan disimpan di storage/app/telegram_session/
 */
class TelegramClientService
{
    private ?string $apiId;
    private ?string $apiHash;
    private ?string $phoneNumber;
    private string $sessionPath;
    private ?object $client = null;

    public function __construct()
    {
        $this->apiId = config('services.telegram.client_api_id');
        $this->apiHash = config('services.telegram.client_api_hash');
        $this->phoneNumber = config('services.telegram.client_phone');
        $this->sessionPath = storage_path('app/telegram_session');
        
        // Ensure session directory exists
        if (!file_exists($this->sessionPath)) {
            mkdir($this->sessionPath, 0755, true);
        }
    }

    /**
     * Send message via Telegram Client API (from personal account)
     * 
     * Catatan: 
     * - Untuk pertama kali, perlu login manual via: php artisan telegram:client-auth
     * - Target bisa berupa: nomor HP, username (@username), atau user ID
     * - Jika menggunakan nomor HP, user harus ada di kontak Anda atau sudah pernah chat
     */
    public function sendMessage(string|int $target, string $message): bool
    {
        // Remember initial output buffer level
        $initialObLevel = ob_get_level();
        
        try {
            if (!$this->isConfigured()) {
                Log::warning('Telegram Client not configured');
                return false;
            }

            // Cek apakah ada library MadelineProto atau gunakan fallback
            if (class_exists('\danog\MadelineProto\API')) {
                $result = $this->sendViaMadelineProto($target, $message);
                // sendViaMadelineProto handles its own output buffering
                // Restore to initial level
                while (ob_get_level() > $initialObLevel) {
                    @ob_end_clean();
                }
                return $result;
            }
            
            // Fallback: Simple HTTP approach (butuh session yang sudah ada)
            return $this->sendViaSimpleClient($target, $message);
        } catch (\Throwable $e) {
            // Restore output buffer level
            while (ob_get_level() > $initialObLevel) {
                @ob_end_clean();
            }
            
            Log::error('Telegram Client sendMessage failed', [
                'error' => $e->getMessage(),
                'target' => $target,
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Send order status update via client API
     */
    public function sendOrderStatusUpdate(Order $order, string $status, User $actor): bool
    {
        // Remember initial output buffer level
        $initialObLevel = ob_get_level();
        
        try {
            $customer = $order->customer;
            if (!$customer || !$customer->phone) {
                Log::warning('Telegram Client: Customer or phone not available', [
                    'order_id' => $order->id,
                    'customer_id' => $customer?->id,
                    'has_phone' => !empty($customer?->phone)
                ]);
                return false;
            }

            $message = $this->buildStatusMessage($order, $status, $actor);
            
            // sendMessage() handles its own output buffering, so we don't need to manage it here
            $result = $this->sendMessage($customer->phone, $message);
            
            // Restore output buffer level to initial state (sendMessage cleans its own buffers)
            while (ob_get_level() > $initialObLevel) {
                @ob_end_clean();
            }
            
            if ($result) {
                Log::info('Telegram notification sent successfully', [
                    'order_id' => $order->id,
                    'status' => $status,
                    'customer_phone' => $customer->phone
                ]);
            }
            
            return $result;
        } catch (\Throwable $e) {
            // Restore output buffer level to initial state
            while (ob_get_level() > $initialObLevel) {
                @ob_end_clean();
            }
            
            Log::error('Telegram Client sendOrderStatusUpdate failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'status' => $status,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send via MadelineProto (if installed)
     * 
     * @param string|int $target Phone number, username (@username), or user ID
     */
    private function sendViaMadelineProto(string|int $target, string $message): bool
    {
        // Suppress ALL output from MadelineProto (including warnings, errors, logger output)
        // Use multiple levels of output buffering to catch everything
        $obLevel = ob_get_level();
        for ($i = 0; $i < 10; $i++) {
            ob_start();
        }
        
        // Also suppress error reporting temporarily and redirect all output
        $oldErrorReporting = error_reporting(0);
        ini_set('display_errors', '0');
        
        // Suppress stderr output as well (for Windows compatibility)
        if (function_exists('ini_set')) {
            ini_set('log_errors', '0');
        }
        
        // Redirect stdout and stderr if possible (for Windows)
        $oldStdout = null;
        $oldStderr = null;
        if (PHP_OS_FAMILY === 'Windows' && function_exists('fopen')) {
            // Try to redirect output to null device
            try {
                $null = fopen('NUL', 'w');
                if ($null !== false) {
                    $oldStdout = ini_get('error_log');
                }
            } catch (\Throwable $e) {
                // Ignore if can't redirect
            }
        }
        
        try {
            $sessionFile = $this->sessionPath . '/session.madeline';
            
            if (!file_exists($sessionFile)) {
                $this->cleanAllOutputBuffers();
                error_reporting($oldErrorReporting);
                Log::error('Telegram session not found. Run: php artisan telegram:client-auth');
                return false;
            }

            // Initialize MadelineProto with session and settings to disable IPC
            // Create settings to disable IPC server (fix for Windows TCP connection issues)
            // @phpstan-ignore-next-line - MadelineProto loaded conditionally
            $appInfo = (new \danog\MadelineProto\Settings\AppInfo)
                ->setApiId((int) $this->apiId)
                ->setApiHash($this->apiHash);
            
            // Create main settings object
            // @phpstan-ignore-next-line - MadelineProto loaded conditionally
            $settings = new \danog\MadelineProto\Settings;
            $settings->setAppInfo($appInfo);
            
            // Disable IPC server by setting slow mode (fixes Windows TCP connection error)
            // Using try-catch in case method doesn't exist in this version
            try {
                // @phpstan-ignore-next-line - MadelineProto loaded conditionally
                $settings->getIpc()->setSlow(true);
            } catch (\Throwable $e) {
                // Fallback: try setEnabled(false) if setSlow doesn't exist
                try {
                    // @phpstan-ignore-next-line - MadelineProto loaded conditionally
                    $settings->getIpc()->setEnabled(false);
                } catch (\Throwable $e2) {
                    // If both fail, continue without IPC settings (may still work)
                    Log::warning('Could not configure IPC settings', ['error' => $e2->getMessage()]);
                }
            }
            
            // Disable IPv6 for Windows compatibility
            try {
                // @phpstan-ignore-next-line - MadelineProto loaded conditionally
                $settings->getConnection()->setIpv6(false);
            } catch (\Throwable $e) {
                // Continue without IPv6 settings if method doesn't exist
                Log::warning('Could not configure connection settings', ['error' => $e->getMessage()]);
            }
            
            // Initialize MadelineProto with session and settings
            // @phpstan-ignore-next-line - MadelineProto loaded conditionally
            $madeline = new \danog\MadelineProto\API($sessionFile, $settings);
            
            // Try to start/restore session (will auto-use existing authenticated session)
            // Don't call getSelf() first as it throws exception if not ready
            try {
                // @phpstan-ignore-next-line - MadelineProto API may vary
                $madeline->start();
            } catch (\Throwable $startError) {
                $this->cleanAllOutputBuffers();
                error_reporting($oldErrorReporting);
                Log::error('Failed to start MadelineProto', [
                    'error' => $startError->getMessage(),
                    'trace' => $startError->getTraceAsString()
                ]);
                
                // If it's asking for code, session is expired or not authenticated
                if (str_contains($startError->getMessage(), 'code') || 
                    str_contains($startError->getMessage(), 'Enter') ||
                    str_contains($startError->getMessage(), 'QR code')) {
                    Log::error('Telegram session expired or not authenticated. Please run: php artisan telegram:client-auth');
                    return false;
                }
                throw $startError;
            }
            
            // Clear all output from MadelineProto startup
            $this->cleanAllOutputBuffers();
            
            // Format peer (phone, username, or user ID)
            $peer = $target;
            if (is_string($target) && !str_starts_with($target, '@') && !str_starts_with($target, '+')) {
                $peer = $this->formatPhoneForTelegram($target);
            }
            
            // Resolve peer using comprehensive strategy
            try {
                $resolvedPeer = $this->resolvePeer($madeline, $peer, $target);
                    
                Log::info('Prepared to send message', [
                    'target' => $target,
                    'original_peer' => $peer,
                    'resolved_peer' => $resolvedPeer
                ]);
                
                // Send message - MUST use resolved peer ID
                if (!$resolvedPeer) {
                    throw new \Exception(
                        "Tidak dapat mengirim pesan ke {$target}. " .
                        "Nomor ini tidak terdaftar di Telegram atau belum pernah chat dengan akun ini. " .
                        "Pastikan:\n" .
                        "1. Nomor sudah terdaftar di Telegram\n" .
                        "2. Nomor sudah ada di kontak Telegram akun ini\n" .
                        "3. User dengan nomor tersebut sudah pernah chat dengan akun ini sebelumnya"
                    );
                }
                
                // Use resolved peer ID (not phone number string)
                // @phpstan-ignore-next-line - MadelineProto API may vary
                $result = $madeline->messages->sendMessage([
                    'peer' => $resolvedPeer,
                    'message' => $message,
                ]);
                    
                Log::info('Telegram Client message sent successfully', [
                    'target' => $target,
                    'peer' => $resolvedPeer
                ]);
                    
                // Clear all output buffers before returning
                $this->cleanAllOutputBuffers();
                error_reporting($oldErrorReporting);
                return true;
                    
            } catch (\Throwable $sendError) {
                $errorMsg = $sendError->getMessage();
                
                // If still "not present" error even after resolving
                if (str_contains($errorMsg, 'not present in the internal peer database') || 
                    str_contains($errorMsg, 'not present')) {
                    
                    Log::error('Peer still not in database after resolve attempt', [
                        'peer' => $peer,
                        'resolved_peer' => $resolvedPeer ?? null,
                        'error' => $errorMsg
                    ]);
                    
                    throw new \Exception(
                        "Gagal mengirim pesan ke {$target}. " .
                        "Nomor mungkin tidak terdaftar di Telegram atau tidak bisa di-resolve. " .
                        "Pastikan nomor terdaftar di Telegram dan user sudah pernah aktif."
                    );
                }
                
                // Other errors
                throw $sendError;
            }
        } catch (\danog\MadelineProto\RPCErrorException $e) {
            // Clear all output buffers on error
            $this->cleanAllOutputBuffers();
            error_reporting($oldErrorReporting);
            // Handle specific Telegram errors
            if (str_contains($e->getMessage(), 'not present in the internal peer database')) {
                Log::error('Telegram Client: Contact not found', [
                    'phone' => $target,
                    'note' => 'User must be in your Telegram contacts or have chatted with you before'
                ]);
                throw new \Exception(
                    "Nomor {$target} tidak ditemukan di kontak Telegram Anda. " .
                    "Pastikan nomor tersebut sudah ada di kontak Telegram akun Anda, " .
                    "atau user dengan nomor tersebut sudah pernah chat dengan akun Telegram Anda."
                );
            }
            
            Log::error('MadelineProto RPC error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            throw $e;
        } catch (\Throwable $e) {
            // Check if it's MadelineProto specific error
            if (str_contains($e->getMessage(), 'MadelineProto') || str_contains(get_class($e), 'MadelineProto')) {
                Log::error('MadelineProto send failed', [
                    'error' => $e->getMessage(),
                    'code' => method_exists($e, 'getCode') ? $e->getCode() : null,
                ]);
            }
            Log::error('Telegram Client send failed', ['error' => $e->getMessage()]);
            
            // Clear all output buffers
            $this->cleanAllOutputBuffers();
            error_reporting($oldErrorReporting);
            
            throw $e; // Re-throw instead of returning false
        } finally {
            // Always clear all output buffers and restore error reporting
            $this->cleanAllOutputBuffers();
            error_reporting($oldErrorReporting);
            ini_set('display_errors', '1');
        }
    }
    
    /**
     * Resolve peer to ensure it's in MadelineProto's internal database
     * Returns user ID if resolved successfully, null otherwise
     */
    private function resolvePeer($madeline, string $peer, string|int $target): ?int
    {
        $phoneClean = preg_replace('/[^0-9]/', '', $peer);
        $resolvedPeer = null;
        
        // Check if it's a phone number
        if (str_starts_with($peer, '+') || ctype_digit($phoneClean)) {
            Log::info('Resolving phone number', ['phone' => $phoneClean, 'formatted_peer' => $peer]);
            
            // Method 1: Try getInfo first (will add to DB if exists and accessible)
            try {
                Log::info('Attempting getInfo with phone number', ['peer' => $peer]);
                $peerInfo = $madeline->getInfo($peer);
                $resolvedPeer = $this->extractUserIdFromPeerInfo($peerInfo);
                
                if ($resolvedPeer) {
                    Log::info('Peer resolved via getInfo', [
                        'user_id' => $resolvedPeer,
                        'phone' => $phoneClean
                    ]);
                    return $resolvedPeer;
                }
            } catch (\Throwable $getInfoError) {
                Log::info('Peer not in database yet, will try import', [
                    'error' => $getInfoError->getMessage(),
                    'phone' => $phoneClean
                ]);
            }
            
            // Method 2: Import as contact with correct format
            if (!$resolvedPeer) {
                try {
                    Log::info('Importing contact to resolve peer', ['phone' => $peer]);
                    
                    // Use correct format with '_' => 'inputPhoneContact'
                    $importResult = $madeline->contacts->importContacts([
                        'contacts' => [
                            [
                                '_' => 'inputPhoneContact',
                                'client_id' => random_int(1000000, 999999999),
                                'phone' => $peer, // Use normalized phone with +
                                'first_name' => 'Customer',
                                'last_name' => ''
                            ]
                        ]
                    ]);
                    
                    Log::info('ImportContacts completed', [
                        'phone' => $phoneClean,
                        'has_users' => !empty($importResult['users']),
                        'users_count' => isset($importResult['users']) ? count($importResult['users']) : 0
                    ]);
                    
                    // Validate import result
                    if (!isset($importResult['users']) || count($importResult['users']) === 0) {
                        throw new \Exception('Nomor tidak terdaftar di Telegram. Import contacts tidak mengembalikan user.');
                    }
                    
                    // Get user ID from imported contact
                    $user = $importResult['users'][0];
                    $resolvedPeer = $this->extractUserIdFromUser($user);
                    
                    if ($resolvedPeer) {
                        Log::info('Contact imported and resolved', [
                            'user_id' => $resolvedPeer,
                            'phone' => $phoneClean
                        ]);
                        
                        // Wait a bit for peer to be saved in database
                        usleep(500000); // 0.5 second
                        
                        // Verify with getInfo
                        try {
                            $peerInfo = $madeline->getInfo($peer);
                            $verifiedId = $this->extractUserIdFromPeerInfo($peerInfo);
                            
                            if ($verifiedId && $verifiedId === $resolvedPeer) {
                                Log::info('Peer verified in database after import', ['user_id' => $resolvedPeer]);
                                return $resolvedPeer;
                            }
                        } catch (\Throwable $verifyError) {
                            Log::warning('Could not verify peer after import', [
                                'error' => $verifyError->getMessage(),
                                'user_id' => $resolvedPeer
                            ]);
                            // Still return resolved peer from import
                            return $resolvedPeer;
                        }
                    }
                } catch (\Throwable $importError) {
                    Log::error('Failed to import contact', [
                        'error' => $importError->getMessage(),
                        'phone' => $phoneClean,
                        'trace' => $importError->getTraceAsString()
                    ]);
                    
                    // If import fails, the number might not be registered on Telegram
                    throw new \Exception(
                        "Nomor {$phoneClean} tidak dapat di-resolve. " .
                        "Kemungkinan nomor tidak terdaftar di Telegram atau akun tidak aktif. " .
                        "Error: " . $importError->getMessage()
                    );
                }
            }
        } else {
            // For username or other formats, use getInfo directly
            try {
                Log::info('Resolving non-phone peer', ['peer' => $peer]);
                $peerInfo = $madeline->getInfo($peer);
                $resolvedPeer = $this->extractUserIdFromPeerInfo($peerInfo);
                
                if ($resolvedPeer) {
                    Log::info('Non-phone peer resolved', ['user_id' => $resolvedPeer, 'peer' => $peer]);
                    return $resolvedPeer;
                }
            } catch (\Throwable $getInfoError) {
                Log::warning('getInfo failed for non-phone peer', [
                    'error' => $getInfoError->getMessage(),
                    'peer' => $peer
                ]);
            }
        }
        
        return $resolvedPeer;
    }

    /**
     * Extract user ID from peer info array
     */
    private function extractUserIdFromPeerInfo(array $peerInfo): ?int
    {
        if (isset($peerInfo['User']['id'])) {
            return (int) $peerInfo['User']['id'];
        } elseif (isset($peerInfo['user']['id'])) {
            return (int) $peerInfo['user']['id'];
        } elseif (isset($peerInfo['bot_api_id'])) {
            return (int) $peerInfo['bot_api_id'];
        }
        return null;
    }

    /**
     * Extract user ID from user array
     */
    private function extractUserIdFromUser(array $user): ?int
    {
        if (isset($user['id'])) {
            return (int) $user['id'];
        } elseif (isset($user['user_id'])) {
            return (int) $user['user_id'];
        }
        return null;
    }

    /**
     * Clean all output buffers to prevent MadelineProto output from contaminating responses
     */
    private function cleanAllOutputBuffers(): void
    {
        while (ob_get_level() > 0) {
            @ob_end_clean(); // Use @ to suppress errors if buffer doesn't exist
        }
    }

    /**
     * Format phone number for Telegram (with + and country code)
     */
    private function formatPhoneForTelegram(string $phone): string
    {
        return $this->normalizePhone($phone);
    }

    /**
     * Normalize phone number to international format (+62xxx)
     */
    private function normalizePhone(string $phone): string
    {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If starts with 0, replace with 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        
        // If doesn't start with 62, add it
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        
        // Add + prefix
        return '+' . $phone;
    }

    /**
     * Simple client approach (alternative jika MadelineProto tidak tersedia)
     * 
     * Catatan: Ini adalah pendekatan sederhana yang mungkin perlu disesuaikan
     * tergantung kebutuhan session management
     */
    private function sendViaSimpleClient(string $phoneNumber, string $message): bool
    {
        // Pendekatan sederhana: Gunakan Telegram Bot API sebagai fallback
        // atau buat implementasi custom sesuai kebutuhan
        
        Log::warning('Simple client not fully implemented. Consider using MadelineProto.');
        return false;
    }

    /**
     * Build status message
     */
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

    /**
     * Get status text in Indonesian
     */
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

    /**
     * Check if service is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiId) && !empty($this->apiHash) && !empty($this->phoneNumber);
    }
}

