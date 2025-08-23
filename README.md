# Jastip App - Laravel API

Aplikasi Jastip (Jasa Titip) dengan integrasi RajaOngkir API untuk layanan pengiriman barang dengan dua metode: Manual dan Otomatis (RajaOngkir).

## 🚀 Fitur Utama

### Metode Pengiriman
1. **Manual (Rekomendasi Admin)**
   - Admin dan Kurir mengelola ongkos kirim, status, serta rute secara manual
   - Tracking berdasarkan update dari Admin/Kurir

2. **Otomatis (RajaOngkir API)**
   - Sistem terhubung dengan Komerce API (RajaOngkir)
   - Perhitungan ongkos kirim otomatis
   - Tracking resi real-time

### Level Pengguna
- **Admin**: Kelola data pengguna, pesanan, konfirmasi, assign kurir
- **Kurir**: Lihat daftar pengantaran, update status, laporkan kendala
- **Pelanggan**: Pesan jasa titip, pilih metode pengiriman, lacak status

## 📋 Requirements

- PHP 8.1+
- Laravel 10.x
- MySQL 5.7+
- Composer
- RajaOngkir API Key

## 🛠️ Installation

1. **Clone repository**
   ```bash
   git clone <repository-url>
   cd jastip-app
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Copy environment file**
   ```bash
   cp ".env copy.example" .env
   ```

4. **Configure database in .env**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=jastiskripsi
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Configure RajaOngkir API**
   ```env
   RAJAONGKIR_API_KEY=your_api_key_here
   RAJAONGKIR_BASE_URL=https://rajaongkir.komerce.id/api/v1
   ```

6. **Generate application key**
   ```bash
   php artisan key:generate
   ```

7. **Run migrations**
   ```bash
   php artisan migrate
   ```

8. **Seed database**
   ```bash
   php artisan db:seed
   ```

9. **Start development server**
   ```bash
   php artisan serve
   ```

## 🔑 Default Admin Account

Setelah menjalankan seeder, Anda dapat login dengan akun admin default:

- **Email**: admin@jastip.com
- **Password**: admin123

## 📡 API Endpoints

### Public Endpoints

#### Authentication
- `POST /api/register` - Register user baru
- `POST /api/login` - Login user

#### RajaOngkir Integration
- `GET /api/provinces` - Get daftar provinsi (filtered: Sulawesi Selatan & Papua Barat)
- `GET /api/cities` - Get daftar kota
- `GET /api/couriers` - Get daftar kurir
- `GET /api/search-destinations` - Search destinasi (Direct Search Method)
- `GET /api/cached-destinations` - Get cached destinations untuk provinsi tertentu
- `POST /api/calculate-shipping` - Hitung ongkos kirim

### Protected Endpoints

#### Authentication
- `POST /api/logout` - Logout user
- `GET /api/profile` - Get profile user
- `PUT /api/profile` - Update profile user

#### Orders
- `GET /api/orders` - Get daftar order
- `POST /api/orders` - Create order baru
- `GET /api/orders/{id}` - Get detail order
- `POST /api/orders/{id}/track` - Track order
- `POST /api/orders/{id}/confirm` - Confirm order (Admin only)
- `POST /api/orders/{id}/assign-courier` - Assign courier (Admin only)
- `PUT /api/orders/{id}/status` - Update status order (Admin/Courier)

#### Complaints
- `GET /api/complaints` - Get daftar complaint
- `POST /api/complaints` - Create complaint baru
- `GET /api/complaints/{id}` - Get detail complaint
- `POST /api/complaints/{id}/assign` - Assign complaint (Admin only)
- `POST /api/complaints/{id}/resolve` - Resolve complaint (Admin only)
- `POST /api/complaints/{id}/close` - Close complaint (Admin only)
- `GET /api/complaints/assigned/list` - Get assigned complaints (Admin only)
- `GET /api/complaints/statistics` - Get complaint statistics (Admin only)

## 🔧 RajaOngkir Integration

### Direct Search Method
Aplikasi menggunakan **Direct Search Method** untuk pencarian destinasi yang lebih cepat dan efisien:

```php
// Search destinations
GET /api/search-destinations?search=jakarta&limit=10&offset=0
```

### Caching Strategy
- **Provinces**: Hanya cache untuk Sulawesi Selatan dan Papua Barat
- **Destinations**: Cache hasil pencarian dengan TTL 24 jam
- **Cities & Districts**: Cache berdasarkan province/city ID

### API Endpoints Used
- `GET /destination/domestic-destination` - Search destinations
- `GET /destination/province` - Get provinces
- `GET /destination/city` - Get cities
- `GET /destination/subdistrict` - Get districts
- `POST /calculate/domestic-cost` - Calculate shipping cost
- `POST /track/waybill` - Track shipment

## 🗄️ Database Structure

### Tables
- `roles` - User roles (admin, courier, customer)
- `users` - User data dengan role_id
- `orders` - Order data dengan shipping method
- `order_statuses` - Order status history
- `complaints` - Complaint data

### Key Features
- Soft deletes untuk orders dan complaints
- Status tracking dengan history
- Role-based access control
- RajaOngkir response caching

## 🔐 Authentication & Authorization

### Middleware
- `auth:sanctum` - API authentication
- `admin` - Admin only access
- `admin_or_courier` - Admin or courier access

### Role Permissions
- **Admin**: Full access to all features
- **Courier**: Can update order status, view assigned orders
- **Customer**: Can create orders, view own orders, submit complaints

## 🚀 Deployment

1. **Production environment setup**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Set proper permissions**
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

3. **Configure queue worker (if using queues)**
   ```bash
   php artisan queue:work
   ```

## 📝 API Documentation

### Request Headers
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Response Format
```json
{
    "success": true,
    "message": "Success message",
    "data": {
        // Response data
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        // Validation errors
    }
}
```

## 🧪 Testing

```bash
# Run tests
php artisan test

# Run specific test
php artisan test --filter OrderTest
```

## 📊 Monitoring

- Log files: `storage/logs/laravel.log`
- RajaOngkir API errors are logged with context
- Database queries are logged in debug mode

## 🔄 Caching

### Clear Cache
```bash
# Clear RajaOngkir cache
php artisan tinker
>>> app(App\Services\RajaOngkirService::class)->clearCache();

# Clear all cache
php artisan cache:clear
```

### Cache Keys
- `rajaongkir_provinces_filtered` - Filtered provinces
- `rajaongkir_destinations_{search}_{limit}_{offset}` - Search results
- `rajaongkir_cities_{province_id}` - Cities by province
- `rajaongkir_districts_{city_id}` - Districts by city

## 🤝 Contributing

1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

Untuk bantuan teknis atau pertanyaan, silakan buat issue di repository ini.

---

**Note**: Pastikan API key RajaOngkir Anda valid dan memiliki akses ke endpoint yang diperlukan. Aplikasi ini menggunakan Komerce API untuk integrasi RajaOngkir.
