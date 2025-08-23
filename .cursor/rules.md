Product Requirements Document (PRD) – Jastip App
1. Ringkasan Produk

Aplikasi Jastip adalah platform layanan titip barang dengan dua metode pengiriman:

Manual (Rekomendasi Admin): Admin dan Kurir mengelola ongkos kirim, status, serta rute secara manual.

Otomatis (RajaOngkir API): Sistem terhubung dengan Komerce API (RajaOngkir) untuk perhitungan ongkos kirim & tracking resi otomatis.

2. Level Pengguna
a. Admin

Kelola data pengguna (Kurir & Pelanggan).

Input data kurir.

Kelola pesanan.

Konfirmasi pesanan & tentukan metode pengiriman:

Rekomendasi Admin (Manual) → assign kurir, update status secara manual.

RajaOngkir → gunakan API untuk hitung ongkir, generate resi, dan tracking otomatis.

Tangani keluhan & masalah.

Monitoring laporan & statistik.

b. Kurir

Registrasi & login.

Lihat daftar pengantaran & pickup.

Update status pengiriman (untuk metode manual).

Laporkan kendala (keluhan).

c. Pelanggan

Registrasi & login.

Pesan jasa titip.

Pilih metode pengiriman:

Rekomendasi Admin (ongkir & status ditentukan admin).

RajaOngkir (ongkir dihitung otomatis, tracking resi aktif).

Lacak status pengiriman.

Ajukan keluhan.

3. Metode Pengiriman
1) Rekomendasi Admin (Manual)

Ongkir ditentukan oleh Admin (misalnya tarif flat atau negosiasi langsung).

Admin assign kurir → Kurir update status secara manual (pickup, antar, selesai).

Tracking hanya terlihat berdasarkan update dari Admin/Kurir (bukan API).

2) RajaOngkir (Otomatis)

Pelanggan pilih tujuan → sistem hitung ongkir via Komerce RajaOngkir API.

Admin konfirmasi & generate pesanan.

Resi diberikan (dari ekspedisi partner).

Pelacakan status otomatis via endpoint Tracking Airwaybills post.

Status real-time ditarik langsung dari API.

4. Integrasi API RajaOngkir (via Komerce API)

Base URL: sesuai akun (Starter/Basic/Pro).
Auth: API-Key (disimpan aman di backend).

Endpoint penting:

Cari Tujuan: /search/domestics atau /search/province/city/district.

Hitung Ongkir: /calculate-domestic (body: origin, destination, weight, courier).

Tracking Resi: /tracking-airwaybills.

⚠️ Catatan: Data statis (province, city, district) bisa di-cache di backend; perhitungan ongkir & tracking harus selalu real-time.

5. Alur Fungsional
Pemesanan (Manual)

Pelanggan pesan → Admin tentukan ongkir manual → Admin assign kurir → Kurir update status → Pelanggan cek progress via app.

Pemesanan (RajaOngkir)

Pelanggan pesan → Input tujuan → Aplikasi panggil API untuk ongkir → Pelanggan pilih jasa ekspedisi.

Admin konfirmasi pesanan → Resi dibuat → Pelanggan bisa tracking langsung via API.

6. Non-Functional Requirements

Keamanan: API-Key hanya di backend.

Ketersediaan: fallback ke metode manual jika API gagal/timeout.

UI: jelas membedakan metode pengiriman (label “Manual” vs “RajaOngkir”).

Scalability: support ekspansi jasa pengiriman lain (Grab, Gojek, dsb).

7. MVP Prioritas

Registrasi/Login 3 level user.

Pemesanan dengan opsi Metode Manual dan RajaOngkir.

Admin konfirmasi & kurir update status (manual).

Integrasi ongkir otomatis & tracking (RajaOngkir).

Lacak status (manual & otomatis).

Keluhan & resolusi.