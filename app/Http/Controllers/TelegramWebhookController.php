<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $update = $request->all();
        Log::info('Telegram webhook received', ['update' => $update]);

        $message = $update['message'] ?? $update['edited_message'] ?? null;
        if (!$message) {
            return response()->json(['ok' => true]);
        }

        $chat = $message['chat'] ?? null;
        $from = $message['from'] ?? null;
        $text = trim((string) ($message['text'] ?? ''));

        if (!$chat || empty($chat['id'])) {
            return response()->json(['ok' => true]);
        }

        $chatId = (string) $chat['id'];

        // Simple linking flow:
        // - If user sends "/start" we try to match by phone (if available) later via manual admin action
        // - If user sends "/link <phone>" we try to find the user by phone and store chat id
        if (str_starts_with($text, '/link')) {
            $parts = preg_split('/\s+/', $text);
            $phoneArg = $parts[1] ?? null;
            if (!$phoneArg) {
                $this->reply($chatId, "Format salah. Gunakan: /link 08xxxxxxxxxx");
                return response()->json(['ok' => true]);
            }

            $normalized = preg_replace('/[^0-9]/', '', $phoneArg);
            if (str_starts_with($normalized, '62')) {
                $alt = '0' . substr($normalized, 2);
            } else {
                $alt = '62' . ltrim($normalized, '0');
            }

            $user = User::where('phone', $normalized)
                ->orWhere('phone', $alt)
                ->first();

            if (!$user) {
                $this->reply($chatId, "Nomor tidak ditemukan. Pastikan nomor HP terdaftar di akun Anda.");
                return response()->json(['ok' => true]);
            }

            $user->telegram_chat_id = $chatId;
            $user->save();

            $this->reply($chatId, "Berhasil terhubung. Anda akan menerima update status pesanan di sini.");
            return response()->json(['ok' => true]);
        }

        if ($text === '/start') {
            $this->reply($chatId, "Halo! 👋\n\nUntuk menghubungkan akun Anda dengan bot ini, kirim perintah:\n\n/link 08xxxxxxxxxx\n\nContoh: /link 081234567896\n\nNomor HP harus sama dengan yang terdaftar di akun Anda.");
            return response()->json(['ok' => true]);
        }

        // Try to auto-link if user sends phone number without /link command
        if (preg_match('/^0[0-9]{9,12}$/', $text) || preg_match('/^62[0-9]{9,12}$/', $text)) {
            $normalized = preg_replace('/[^0-9]/', '', $text);
            $alt = str_starts_with($normalized, '62') 
                ? '0' . substr($normalized, 2) 
                : '62' . ltrim($normalized, '0');

            $user = User::where('phone', $normalized)
                ->orWhere('phone', $alt)
                ->first();

            if ($user) {
                $user->telegram_chat_id = $chatId;
                $user->save();
                $this->reply($chatId, "✅ Berhasil terhubung!\n\nAnda akan menerima update status pesanan di sini secara otomatis.\n\nKirim /help untuk bantuan lebih lanjut.");
            } else {
                $this->reply($chatId, "❌ Nomor tidak ditemukan.\n\nPastikan nomor HP yang Anda kirim sama dengan yang terdaftar di akun Anda.\n\nAtau gunakan: /link 08xxxxxxxxxx");
            }
            return response()->json(['ok' => true]);
        }

        return response()->json(['ok' => true]);
    }

    private function reply(string $chatId, string $text): void
    {
        try {
            $botToken = (string) config('services.telegram.bot_token');
            if (empty($botToken)) {
                return;
            }
            $url = 'https://api.telegram.org/bot' . $botToken . '/sendMessage';
            \Illuminate\Support\Facades\Http::post($url, [
                'chat_id' => $chatId,
                'text' => $text,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Telegram reply failed', ['error' => $e->getMessage()]);
        }
    }
}

