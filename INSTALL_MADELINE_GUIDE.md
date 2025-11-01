# Cara Install MadelineProto (Jika Ada Masalah Koneksi)

## Masalah: Timeout saat install

Jika mengalami timeout seperti:
```
curl error 28 while downloading https://repo.packagist.org/...
```

## Solusi:

### 1. Coba Lagi (Masalah Jaringan Sementara)
```powershell
composer require danog/madelineproto
```

### 2. Gunakan Mirror Packagist (Indonesia)
```powershell
composer config -g repo.packagist composer https://packagist.phpcomposer.com
composer require danog/madelineproto
```

### 3. Install Manual dari GitHub
```powershell
# Clone repository
git clone https://github.com/danog/MadelineProto.git vendor/danog/madelineproto

# Atau download ZIP dan extract ke vendor/danog/madelineproto
```

### 4. Gunakan VPN/Proxy (Jika Terblokir)
- Aktifkan VPN sebelum install
- Atau set proxy di composer:
```powershell
composer config -g http-basic.proxy-host your-proxy-host
composer config -g http-basic.proxy-port your-proxy-port
```

## Alternatif: Pakai Bot API Saja

**Jika MadelineProto tetap tidak bisa diinstall**, sistem sudah punya **fallback otomatis**:
- Sistem akan otomatis pakai **Bot API** jika Client API tidak tersedia
- Bot API sudah bekerja dan tidak perlu library tambahan

Untuk pakai Bot API saja:
1. **Tidak perlu install MadelineProto**
2. Pastikan `TELEGRAM_BOT_TOKEN` sudah di `.env`
3. Customer harus chat bot dulu untuk dapat `chat_id`

## Status Sistem Sekarang:

✅ **Bot API sudah bekerja** (tanpa perlu install apapun)
✅ **Client API siap** (tapi butuh MadelineProto)
✅ **Auto fallback** - Jika Client API gagal, pakai Bot API

Jadi **Anda bisa langsung pakai Bot API** untuk testing tanpa perlu install MadelineProto!

