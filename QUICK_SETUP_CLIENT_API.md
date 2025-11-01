# Quick Setup: Telegram Client API (Kirim dari Akun Pribadi)

## Untuk Testing Sekarang (Tanpa Install MadelineProto)

Sistem sudah diatur agar **prioritas Client API dulu**, tapi karena MadelineProto belum terinstall, sistem akan otomatis fallback ke Bot API.

## Setup Minimal untuk Testing:

### 1. Tambahkan ke `.env` (untuk activate Client API check):

```env
TELEGRAM_CLIENT_API_ID=placeholder
TELEGRAM_CLIENT_API_HASH=placeholder  
TELEGRAM_CLIENT_PHONE=your_phone_number
```

**Catatan:** `placeholder` hanya untuk trigger sistem agar coba Client API. Jika MadelineProto belum terinstall, akan auto fallback ke Bot API.

### 2. Jika Mau Pakai Client API (Butuh MadelineProto):

**Install MadelineProto:**
```powershell
# Coba lagi (mungkin masalah jaringan sementara)
composer require danog/madelineproto

# Atau pakai mirror:
composer config -g repo.packagist composer https://packagist.phpcomposer.com
composer require danog/madelineproto
```

**Dapatkan API ID & Hash:**
1. Buka https://my.telegram.org/apps
2. Login dengan nomor Telegram Anda
3. Copy `api_id` dan `api_hash`

**Update `.env`:**
```env
TELEGRAM_CLIENT_API_ID=your_real_api_id
TELEGRAM_CLIENT_API_HASH=your_real_api_hash
TELEGRAM_CLIENT_PHONE=your_phone_number
```

**Autentikasi:**
```powershell
php artisan telegram:client-auth
```

## Cara Test Sekarang:

```powershell
# Test dengan order (akan coba Client API dulu, lalu fallback Bot API)
php artisan telegram:test --order=1 --status=delivered

# Test langsung ke nomor HP via Client API
php artisan telegram:test --phone=081234567896
```

## Status Sistem:

✅ **OrderService** sudah diupdate:
- Cek Client API dulu → jika configured & berhasil → kirim via Client API
- Jika Client API tidak configured atau gagal → fallback ke Bot API

✅ **Test Command** sudah diupdate:
- Cek Client API dulu
- Tampilkan info jelas apakah pakai Client API atau Bot API

## Catatan Penting:

⚠️ **Tanpa MadelineProto:**
- Client API tidak bisa bekerja
- Sistem akan otomatis fallback ke Bot API
- Bot API tetap butuh `telegram_chat_id` (customer harus chat bot dulu)

✅ **Dengan MadelineProto:**
- Client API bisa kirim langsung ke nomor HP
- Tidak perlu `telegram_chat_id`
- Customer tidak perlu chat bot dulu

## Untuk Testing Sekarang:

Jika tidak bisa install MadelineProto, **pakai Bot API**:
1. Customer chat bot di Telegram
2. Kirim `/start` atau nomor HP
3. Bot otomatis simpan `chat_id`
4. Test lagi: `php artisan telegram:test --order=1 --status=delivered`

