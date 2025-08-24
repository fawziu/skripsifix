# Perbaikan Validasi Request Pickup

## Masalah yang Diperbaiki

1. **Validation failed** saat request pickup
2. **Courier Service: N/A** di waybill view
3. **Error handling** yang kurang informatif

## Perbaikan yang Dilakukan

### 1. Backend Validation (WaybillController.php)

**Sebelum:**
```php
'pickup_date' => 'required|date|after:today',
'pickup_time' => 'required|string',
'pickup_address' => 'required|string',
'pickup_contact' => 'required|string',
'pickup_phone' => 'required|string',
'notes' => 'sometimes|string',
```

**Sesudah:**
```php
'pickup_date' => 'required|date|after_or_equal:today',
'pickup_time' => 'required|string|in:08:00-10:00,10:00-12:00,13:00-15:00,15:00-17:00',
'pickup_address' => 'required|string|min:10',
'pickup_contact' => 'required|string|min:2',
'pickup_phone' => 'required|string|min:10',
'notes' => 'nullable|string|max:500',
```

### 2. Frontend Validation (waybill.blade.php)

- **Tanggal Pickup**: Minimal hari ini (`min="{{ date('Y-m-d') }}"`)
- **Waktu Pickup**: Dropdown dengan opsi yang valid
- **Alamat**: Minimal 10 karakter dengan placeholder
- **Kontak**: Minimal 2 karakter
- **Telepon**: Minimal 10 digit
- **Catatan**: Opsional, maksimal 500 karakter

### 3. Error Handling

**Backend:**
```php
'message' => 'Validasi gagal: ' . $validator->errors()->first(),
```

**Frontend:**
```javascript
// Menampilkan error detail
if (result.errors) {
    errorMessage += '\n\nDetail error:';
    Object.keys(result.errors).forEach(field => {
        errorMessage += '\n- ' + field + ': ' + result.errors[field].join(', ');
    });
}
```

### 4. Courier Service Display

**Sebelum:**
```php
{{ strtoupper($order->courier_service ?? 'N/A') }}
```

**Sesudah:**
```php
@if($order->courier_service)
    {{ strtoupper($order->courier_service) }}
@elseif($order->shipping_method === 'rajaongkir')
    <span class="text-orange-600 italic">Belum dipilih</span>
@else
    <span class="text-gray-500 italic">Manual Delivery</span>
@endif
```

## Fitur Tambahan

### 1. Frontend Validation
- Validasi real-time sebelum submit
- Pesan error yang jelas dan informatif
- Mencegah submit form yang tidak valid

### 2. Pickup Info Modal
- Menampilkan informasi pickup yang sudah dijadwalkan
- Mencegah request pickup ganda
- Status pickup yang jelas

### 3. WhatsApp Notification
- Notifikasi otomatis saat request pickup
- Link WhatsApp dengan pesan yang sudah diformat
- Informasi pickup yang lengkap

## Cara Penggunaan

1. **Request Pickup:**
   - Klik tombol "Request Pickup"
   - Isi form dengan data yang valid
   - Submit form
   - Pilih apakah ingin kirim notifikasi WhatsApp

2. **Lihat Info Pickup:**
   - Jika sudah ada request pickup, tombol berubah menjadi "Info Pickup"
   - Klik untuk melihat detail pickup yang dijadwalkan

3. **Status Otomatis:**
   - Status akan berubah otomatis menjadi "picked_up" saat waktu pickup tiba
   - Command `pickup:update-status` berjalan setiap 3 menit

## Testing

Untuk test validasi:
1. Coba submit form kosong
2. Coba pilih tanggal kemarin
3. Coba isi alamat kurang dari 10 karakter
4. Coba isi telepon kurang dari 10 digit

Semua error akan ditampilkan dengan jelas di frontend dan backend.
