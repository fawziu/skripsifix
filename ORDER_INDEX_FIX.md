# Perbaikan Kolom Total Biaya di Orders Index

## Masalah yang Ditemukan
Di halaman `orders/index.blade.php`, kolom "Total Biaya" menampilkan "Rp 0" karena menggunakan field yang salah.

## Analisis Masalah
1. **Field yang Salah**: View menggunakan `$order->total_cost` yang tidak ada di database
2. **Field yang Benar**: Database memiliki field `total_amount` yang berisi nilai yang benar
3. **Data Tersedia**: Database memiliki data order dengan nilai yang valid:
   - Order ID 1: Total Amount = Rp 29.000
   - Order ID 2: Total Amount = Rp 198.000

## Perbaikan yang Dilakukan

### 1. Update View `resources/views/orders/index.blade.php`
```php
// Sebelum (SALAH)
Rp {{ number_format($order->total_cost, 0, ',', '.') }}

// Sesudah (BENAR)
Rp {{ number_format($order->total_amount, 0, ',', '.') }}
```

### 2. Struktur Database yang Benar
Model Order memiliki field:
- `item_price`: Harga barang
- `service_fee`: Biaya layanan
- `shipping_cost`: Biaya pengiriman
- `total_amount`: Total keseluruhan (sudah dihitung otomatis)

## Hasil Setelah Perbaikan
- Kolom "Total Biaya" akan menampilkan nilai yang benar
- Data RajaOngkir akan terbaca dengan baik
- Format currency Indonesia (Rp) akan ditampilkan dengan benar

## Testing
Untuk memastikan perbaikan berfungsi:
1. Buka halaman orders index
2. Perhatikan kolom "Total Biaya"
3. Pastikan nilai yang ditampilkan sesuai dengan data di database
4. Test dengan berbagai jenis order (manual dan RajaOngkir)

## Catatan Tambahan
- Field `total_amount` sudah di-cast sebagai decimal di model Order
- Nilai total dihitung otomatis saat order dibuat
- Format currency menggunakan `number_format()` untuk tampilan yang rapi
