@extends('layouts.app')

@section('title', 'Buat Pesanan - Afiyah')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Buat Pesanan Baru</h1>
                <p class="mt-2 text-gray-600">Isi detail pesanan pengiriman Anda</p>
            </div>
            <a href="{{ route('orders.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Order Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form method="POST" action="{{ route('orders.store') }}" x-data="orderForm()" x-init="init()" class="p-6">
            @csrf

            <div class="space-y-6">
                <!-- Progress Indicator -->
                <div class="mb-6">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-box text-blue-600 text-sm"></i>
                            </div>
                            <span class="text-sm font-medium text-blue-900">Detail Barang</span>
                        </div>
                        <div class="flex-1 h-0.5 bg-gray-200"></div>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-shipping-fast text-gray-400 text-sm"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-500">Pengiriman</span>
                        </div>
                        <div class="flex-1 h-0.5 bg-gray-200"></div>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-calculator text-gray-400 text-sm"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-500">Biaya</span>
                        </div>
                    </div>
                </div>

                <!-- Item Details -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Barang</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Item Description -->
                        <div class="md:col-span-2">
                            <label for="item_description" class="block text-sm font-medium text-gray-700 mb-1">
                                Deskripsi Barang <span class="text-red-500">*</span>
                            </label>
                            <textarea id="item_description" name="item_description" rows="3" required
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('item_description') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                      placeholder="Jelaskan detail barang yang akan dikirim">{{ old('item_description') }}</textarea>
                            @error('item_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Item Weight -->
                        <div>
                            <label for="item_weight" class="block text-sm font-medium text-gray-700 mb-1">
                                Berat Barang (kg) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                                            <input type="number" id="item_weight" name="item_weight" step="0.1" min="0.1" required
                                   @change="calculateShipping()"
                                   @input="updateCourierFees()"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('item_weight') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                   placeholder="0.5"
                                   value="{{ old('item_weight') }}">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">kg</span>
                                </div>
                            </div>
                            @error('item_weight')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Item Price -->
                        <div>
                            <label for="item_price" class="block text-sm font-medium text-gray-700 mb-1">
                                Nilai Barang (Rp) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">Rp</span>
                                </div>
                                <input type="number" id="item_price" name="item_price" min="0" required
                                       @input="updateCostSummary()"
                                       class="w-full border border-gray-300 rounded-lg pl-12 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('item_price') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                       placeholder="100000"
                                       value="{{ old('item_price') }}">
                            </div>
                            @error('item_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Shipping Details -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Alamat Pengiriman</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Shipping Method -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Metode Pengiriman <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="relative flex cursor-pointer rounded-lg border-2 transition-all duration-200 p-4 shadow-sm focus:outline-none"
                                       :class="shippingMethod === 'manual' ? 'border-blue-500 bg-blue-50 shadow-md' : 'border-gray-300 bg-white hover:border-gray-400'">
                                    <input type="radio" name="shipping_method" value="manual" @change="toggleShippingMethod()" :checked="shippingMethod === 'manual'" class="sr-only" required>
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <div class="flex items-center space-x-2">
                                                <span class="block text-sm font-medium" :class="shippingMethod === 'manual' ? 'text-blue-900' : 'text-gray-900'">Manual</span>
                                                <span x-show="shippingMethod === 'manual'" class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                                    <i class="fas fa-check mr-1"></i>Dipilih
                                                </span>
                                            </div>
                                            <span class="mt-1 flex items-center text-sm" :class="shippingMethod === 'manual' ? 'text-blue-700' : 'text-gray-500'">
                                                <i class="fas fa-truck mr-2"></i>
                                                Pengiriman manual dengan kurir aktif
                                            </span>
                                        </span>
                                    </span>
                                    <div class="flex-shrink-0">
                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200"
                                             :class="shippingMethod === 'manual' ? 'border-blue-500 bg-blue-500' : 'border-gray-300'">
                                            <i x-show="shippingMethod === 'manual'" class="fas fa-check text-white text-xs"></i>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative flex cursor-pointer rounded-lg border-2 transition-all duration-200 p-4 shadow-sm focus:outline-none"
                                       :class="shippingMethod === 'rajaongkir' ? 'border-green-500 bg-green-50 shadow-md' : 'border-gray-300 bg-white hover:border-gray-400'">
                                    <input type="radio" name="shipping_method" value="rajaongkir" @change="toggleShippingMethod()" :checked="shippingMethod === 'rajaongkir'" class="sr-only" required>
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <div class="flex items-center space-x-2">
                                                <span class="block text-sm font-medium" :class="shippingMethod === 'rajaongkir' ? 'text-green-900' : 'text-gray-900'">RajaOngkir</span>
                                                <span x-show="shippingMethod === 'rajaongkir'" class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                    <i class="fas fa-check mr-1"></i>Dipilih
                                                </span>
                                            </div>
                                            <span class="mt-1 flex items-center text-sm" :class="shippingMethod === 'rajaongkir' ? 'text-green-700' : 'text-gray-500'">
                                                <i class="fas fa-shipping-fast mr-2"></i>
                                                Pengiriman dengan RajaOngkir
                                            </span>
                                        </span>
                                    </span>
                                    <div class="flex-shrink-0">
                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200"
                                             :class="shippingMethod === 'rajaongkir' ? 'border-green-500 bg-green-500' : 'border-gray-300'">
                                            <i x-show="shippingMethod === 'rajaongkir'" class="fas fa-check text-white text-xs"></i>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @error('shipping_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Shipping Method Status Bar -->
                        <div class="md:col-span-2 mb-4">
                            <div x-show="shippingMethod === 'manual'" class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-truck text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-blue-900">Pengiriman Manual Aktif</h4>
                                        <p class="text-sm text-blue-700">Pilih kurir internal untuk pengiriman manual</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                            <i class="fas fa-check mr-1"></i>Manual
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div x-show="shippingMethod === 'rajaongkir'" class="bg-green-50 border border-green-200 rounded-lg p-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-shipping-fast text-green-600"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-green-900">Pengiriman RajaOngkir Aktif</h4>
                                        <p class="text-sm text-green-700">Pilih provinsi, kota, dan kurir untuk perhitungan ongkir</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                            <i class="fas fa-check mr-1"></i>RajaOngkir
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method Selection (Only for Manual Shipping) -->
                        <div x-show="shippingMethod === 'manual'" class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Metode Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="relative flex cursor-pointer rounded-lg border-2 transition-all duration-200 p-4 shadow-sm focus:outline-none"
                                       :class="paymentMethod === 'cod' ? 'border-blue-500 bg-blue-50 shadow-md' : 'border-gray-300 bg-white hover:border-gray-400'">
                                    <input type="radio" name="payment_method" value="cod" @change="togglePaymentMethod()" :checked="paymentMethod === 'cod'" class="sr-only" :required="shippingMethod === 'manual'" :disabled="shippingMethod !== 'manual'">
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <div class="flex items-center space-x-2">
                                                <span class="block text-sm font-medium" :class="paymentMethod === 'cod' ? 'text-blue-900' : 'text-gray-900'">COD (Cash on Delivery)</span>
                                                <span x-show="paymentMethod === 'cod'" class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                                    <i class="fas fa-check mr-1"></i>Dipilih
                                                </span>
                                            </div>
                                            <span class="mt-1 flex items-center text-sm" :class="paymentMethod === 'cod' ? 'text-blue-700' : 'text-gray-500'">
                                                <i class="fas fa-money-bill-wave mr-2"></i>
                                                Bayar saat barang diterima
                                            </span>
                                        </span>
                                    </span>
                                    <div class="flex-shrink-0">
                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200"
                                             :class="paymentMethod === 'cod' ? 'border-blue-500 bg-blue-500' : 'border-gray-300'">
                                            <i x-show="paymentMethod === 'cod'" class="fas fa-check text-white text-xs"></i>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative flex cursor-pointer rounded-lg border-2 transition-all duration-200 p-4 shadow-sm focus:outline-none"
                                       :class="paymentMethod === 'transfer' ? 'border-green-500 bg-green-50 shadow-md' : 'border-gray-300 bg-white hover:border-gray-400'">
                                    <input type="radio" name="payment_method" value="transfer" @change="togglePaymentMethod()" :checked="paymentMethod === 'transfer'" class="sr-only" :required="shippingMethod === 'manual'" :disabled="shippingMethod !== 'manual'">
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <div class="flex items-center space-x-2">
                                                <span class="block text-sm font-medium" :class="paymentMethod === 'transfer' ? 'text-green-900' : 'text-gray-900'">Transfer Bank</span>
                                                <span x-show="paymentMethod === 'transfer'" class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                    <i class="fas fa-check mr-1"></i>Dipilih
                                                </span>
                                            </div>
                                            <span class="mt-1 flex items-center text-sm" :class="paymentMethod === 'transfer' ? 'text-green-700' : 'text-gray-500'">
                                                <i class="fas fa-university mr-2"></i>
                                                Transfer ke rekening kurir
                                            </span>
                                        </span>
                                    </span>
                                    <div class="flex-shrink-0">
                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200"
                                             :class="paymentMethod === 'transfer' ? 'border-green-500 bg-green-500' : 'border-gray-300'">
                                            <i x-show="paymentMethod === 'transfer'" class="fas fa-check text-white text-xs"></i>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Method Status Bar (Only for Manual Shipping) -->
                        <div x-show="shippingMethod === 'manual'" class="md:col-span-2 mb-4">
                            <div x-show="paymentMethod === 'cod'" class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-money-bill-wave text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-blue-900">Pembayaran COD Aktif</h4>
                                        <p class="text-sm text-blue-700">Pembayaran dilakukan saat barang diterima</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                            <i class="fas fa-check mr-1"></i>COD
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div x-show="paymentMethod === 'transfer'" class="bg-green-50 border border-green-200 rounded-lg p-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-university text-green-600"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-green-900">Pembayaran Transfer Aktif</h4>
                                        <p class="text-sm text-green-700">Transfer ke rekening kurir sebelum pengiriman</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                            <i class="fas fa-check mr-1"></i>Transfer
                                        </span>
                                    </div>
                                    <!-- Show courier bank info for transfer payment -->
                                    <div x-show="paymentMethod === 'transfer' && selectedCourierBankInfo" class="mt-3 p-3 bg-white border border-green-200 rounded-lg">
                                        <h5 class="text-sm font-medium text-green-900 mb-2">Informasi Rekening Kurir:</h5>
                                        <div class="text-sm text-green-700">
                                            <p><strong>Bank:</strong> <span x-text="selectedCourierBankInfo ? selectedCourierBankInfo.bank_name : ''"></span></p>
                                            <p><strong>No. Rek:</strong> <span x-text="selectedCourierBankInfo ? selectedCourierBankInfo.account_number : ''" class="font-mono"></span></p>
                                            <p><strong>Atas Nama:</strong> <span x-text="selectedCourierBankInfo ? selectedCourierBankInfo.account_holder : ''"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Courier Selection (Hidden by default) -->
                        <div id="manual_courier_fields" class="hidden md:col-span-2">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                <h4 class="text-sm font-medium text-green-900 mb-2">Pilih Kurir Manual</h4>
                                <div class="space-y-3">
                                    <div id="courier_loading" class="text-center py-4">
                                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-600 mx-auto"></div>
                                        <p class="mt-2 text-sm text-green-700">Memuat daftar kurir...</p>
                                    </div>
                                    <div id="courier_list" class="hidden space-y-2">
                                        <!-- Courier options will be loaded here -->
                                    </div>
                                    <div id="no_couriers" class="hidden text-center py-4">
                                        <p class="text-sm text-gray-500">Tidak ada kurir aktif saat ini</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Origin Address -->
                        <div class="md:col-span-2">
                            <label for="origin_address" class="block text-sm font-medium text-gray-700 mb-1">
                                Alamat Asal <span class="text-red-500">*</span>
                            </label>
                            
                            <!-- Location Controls -->
                            <div class="mb-3 flex items-center space-x-3">
                                <button type="button" id="get-location-btn" 
                                        class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    Dapatkan Lokasi Saya
                                </button>
                                <button type="button" id="clear-location-btn" 
                                        class="inline-flex items-center px-3 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors hidden">
                                    <i class="fas fa-times mr-2"></i>
                                    Hapus Lokasi
                                </button>
                                <div id="location-status" class="text-sm text-gray-500 hidden">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>
                                    Mendapatkan lokasi...
                                </div>
                            </div>

                            <!-- Address Input -->
                            <textarea id="origin_address" name="origin_address" rows="3" required
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('origin_address') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                      placeholder="Masukkan alamat asal pengiriman atau gunakan tombol 'Dapatkan Lokasi Saya'">{{ old('origin_address') }}</textarea>
                            
                            <!-- Location Info Display -->
                            <div id="location-info" class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg hidden">
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-map-marker-alt text-blue-600 mt-1"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-blue-900">Lokasi GPS Terdeteksi</h4>
                                        <p class="text-sm text-blue-700 mt-1" id="location-address">Alamat akan ditampilkan di sini</p>
                                        <div class="mt-2 text-xs text-blue-600">
                                            <span>Koordinat: </span>
                                            <span id="location-coords">-</span>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <button type="button" id="preview-location-btn" 
                                                class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fas fa-eye mr-1"></i>Lihat di Peta
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden fields for GPS coordinates -->
                            <input type="hidden" id="origin_latitude" name="origin_latitude" value="{{ old('origin_latitude') }}">
                            <input type="hidden" id="origin_longitude" name="origin_longitude" value="{{ old('origin_longitude') }}">

                            @error('origin_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Destination Address Selection -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Alamat Tujuan <span class="text-red-500">*</span>
                            </label>

                            <!-- Address Selection -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Alamat Tujuan</label>
                                <select id="destination_address_id" name="destination_address_id" @change="selectAddress()"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Pilih alamat tujuan</option>
                                    @foreach(Auth::user()->addresses()->with(['province', 'city'])->active()->get() as $address)
                                        <option value="{{ $address->id }}"
                                                data-address="{{ $address->full_address }}"
                                                data-province="{{ $address->province ? $address->province->rajaongkir_id : '' }}"
                                                data-city="{{ $address->city ? $address->city->rajaongkir_id : '' }}">
                                            {{ $address->label }} - {{ $address->full_address }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-500">
                                    <a href="{{ route('addresses.create') }}" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-plus mr-1"></i>Tambah alamat baru
                                    </a>
                                </p>
                            </div>

                            <!-- Manual Address Input -->
                            <div id="manual_address_input" class="hidden">
                                <label for="destination_address" class="block text-sm font-medium text-gray-700 mb-1">
                                    Alamat Tujuan Manual
                                </label>
                                <textarea id="destination_address" name="destination_address" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('destination_address') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                          placeholder="Masukkan alamat tujuan pengiriman">{{ old('destination_address') }}</textarea>
                                @error('destination_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- RajaOngkir Fields (Hidden by default) -->
                        <div id="rajaongkir_fields" class="hidden md:col-span-2">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <h4 class="text-sm font-medium text-blue-900 mb-2">Pengaturan RajaOngkir</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="origin_province" class="block text-sm font-medium text-gray-700 mb-1">
                                            Provinsi Asal
                                        </label>
                                        <select id="origin_province" name="origin_province" @change="loadOriginCities()"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Pilih Provinsi</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="origin_city" class="block text-sm font-medium text-gray-700 mb-1">
                                            Kota Asal
                                        </label>
                                        <select id="origin_city" name="origin_city" @change="calculateShipping()"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Pilih Kota</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="courier_select" class="block text-sm font-medium text-gray-700 mb-1">
                                            Pilih Kurir
                                        </label>
                                        <select id="courier_select" name="courier_select" @change="calculateShipping()"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Pilih Kurir</option>
                                            <option value="jne">JNE</option>
                                            <option value="pos">POS Indonesia</option>
                                            <option value="tiki">TIKI</option>
                                            <option value="sicepat">SiCepat</option>
                                            <option value="jnt">J&T Express</option>
                                            <option value="wahana">Wahana</option>
                                            <option value="ninja">Ninja Express</option>
                                            <option value="lion">Lion Parcel</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cost Summary -->
                <div id="cost_summary" class="hidden">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Biaya</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Nilai Barang:</span>
                                <span id="item_cost">Rp 0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Biaya Pengiriman:</span>
                                <span id="shipping_cost">Rp 0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Biaya Layanan:</span>
                                <span id="service_fee">Rp 0</span>
                            </div>
                            <hr class="my-2">
                            <div class="flex justify-between font-semibold text-lg">
                                <span>Total:</span>
                                <span id="total_cost">Rp 0</span>
                            </div>
                        </div>

                        <!-- Courier Bank Info for Manual Shipping with Transfer Payment -->
                        <div x-show="shippingMethod === 'manual' && paymentMethod === 'transfer' && selectedCourierBankInfo" class="mt-4 p-3 bg-white border border-gray-200 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Informasi Rekening Kurir:</h4>
                            <div class="text-sm text-gray-700 space-y-1">
                                <p><strong>Bank:</strong> <span x-text="selectedCourierBankInfo ? selectedCourierBankInfo.bank_name : ''"></span></p>
                                <p><strong>No. Rek:</strong> <span x-text="selectedCourierBankInfo ? selectedCourierBankInfo.account_number : ''" class="font-mono"></span></p>
                                <p><strong>Atas Nama:</strong> <span x-text="selectedCourierBankInfo ? selectedCourierBankInfo.account_holder : ''"></span></p>
                            </div>
                            <div class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded text-xs text-yellow-800">
                                <i class="fas fa-info-circle mr-1"></i>
                                Lakukan transfer ke rekening di atas sebelum pengiriman
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden Fields for Form Submission -->
                <input type="hidden" name="payment_method" value="" disabled>
                <input type="hidden" id="calculated_shipping_cost" name="shipping_cost" value="0">
                <input type="hidden" id="calculated_service_fee" name="service_fee" value="0">
                <input type="hidden" id="calculated_total_cost" name="total_cost" value="0">
                <input type="hidden" id="selected_courier_service" name="courier_service" value="">
                <input type="hidden" id="selected_courier_cost" name="courier_cost" value="">
                <input type="hidden" id="selected_courier_etd" name="courier_etd" value="">
                <input type="hidden" id="selected_courier_id" name="courier_id" value="">
                <input type="hidden" id="selected_courier_pricing_id" name="courier_pricing_id" value="">

                <!-- Submit Button -->
                <div class="flex justify-end pt-6">
                    <button type="submit"
                            onclick="return validateForm()"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-check mr-2"></i>
                        Buat Pesanan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function orderForm() {
    return {
        selectedAddress: null,
        shippingMethod: 'manual',
        paymentMethod: 'cod',
        selectedCourierBankInfo: null,
        currentLocation: null,

        init() {
            this.updateCostSummary();
            // Set default shipping method to manual
            this.shippingMethod = 'manual';
            // Set default payment method to cod
            this.paymentMethod = 'cod';
            // Trigger initial display
            this.toggleShippingMethod();
            this.togglePaymentMethod();
            // Initialize location functionality
            this.initLocationFeatures();
        },

        initLocationFeatures() {
            const getLocationBtn = document.getElementById('get-location-btn');
            const clearLocationBtn = document.getElementById('clear-location-btn');
            const previewLocationBtn = document.getElementById('preview-location-btn');

            if (getLocationBtn) {
                getLocationBtn.addEventListener('click', () => this.getCurrentLocation());
            }

            if (clearLocationBtn) {
                clearLocationBtn.addEventListener('click', () => this.clearLocation());
            }

            if (previewLocationBtn) {
                previewLocationBtn.addEventListener('click', () => this.previewLocation());
            }
        },

        getCurrentLocation() {
            const statusDiv = document.getElementById('location-status');
            const getLocationBtn = document.getElementById('get-location-btn');
            const clearLocationBtn = document.getElementById('clear-location-btn');

            // Show loading state
            statusDiv.classList.remove('hidden');
            getLocationBtn.disabled = true;
            getLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mendapatkan...';

            if (!navigator.geolocation) {
                this.showLocationError('Geolocation tidak didukung oleh browser ini.');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.handleLocationSuccess(position);
                },
                (error) => {
                    this.handleLocationError(error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 30000,
                    maximumAge: 300000
                }
            );
        },

        handleLocationSuccess(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const accuracy = position.coords.accuracy;

            this.currentLocation = { lat, lng, accuracy };

            // Update hidden fields
            document.getElementById('origin_latitude').value = lat;
            document.getElementById('origin_longitude').value = lng;

            // Get address from coordinates using reverse geocoding
            this.reverseGeocode(lat, lng);

            // Update UI
            this.updateLocationUI();
        },

        handleLocationError(error) {
            const statusDiv = document.getElementById('location-status');
            const getLocationBtn = document.getElementById('get-location-btn');

            statusDiv.classList.add('hidden');
            getLocationBtn.disabled = false;
            getLocationBtn.innerHTML = '<i class="fas fa-map-marker-alt mr-2"></i>Dapatkan Lokasi Saya';

            let errorMessage = 'Gagal mendapatkan lokasi. ';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage += 'Izin lokasi ditolak. Silakan aktifkan izin lokasi di browser Anda.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage += 'Lokasi tidak tersedia. Periksa koneksi GPS Anda.';
                    break;
                case error.TIMEOUT:
                    errorMessage += 'Timeout mendapatkan lokasi. Silakan coba lagi.';
                    break;
                default:
                    errorMessage += 'Terjadi kesalahan yang tidak diketahui.';
                    break;
            }

            alert(errorMessage);
        },

        reverseGeocode(lat, lng) {
            // Use OpenStreetMap Nominatim for reverse geocoding
            const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        const address = data.display_name;
                        document.getElementById('origin_address').value = address;
                        document.getElementById('location-address').textContent = address;
                    } else {
                        document.getElementById('location-address').textContent = `Koordinat: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    }
                })
                .catch(error => {
                    console.error('Reverse geocoding error:', error);
                    document.getElementById('location-address').textContent = `Koordinat: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                });
        },

        updateLocationUI() {
            const statusDiv = document.getElementById('location-status');
            const getLocationBtn = document.getElementById('get-location-btn');
            const clearLocationBtn = document.getElementById('clear-location-btn');
            const locationInfo = document.getElementById('location-info');
            const coordsSpan = document.getElementById('location-coords');

            // Hide loading state
            statusDiv.classList.add('hidden');
            getLocationBtn.disabled = false;
            getLocationBtn.innerHTML = '<i class="fas fa-map-marker-alt mr-2"></i>Dapatkan Lokasi Saya';

            // Show location info
            locationInfo.classList.remove('hidden');
            clearLocationBtn.classList.remove('hidden');

            // Update coordinates display
            if (this.currentLocation) {
                coordsSpan.textContent = `${this.currentLocation.lat.toFixed(6)}, ${this.currentLocation.lng.toFixed(6)}`;
            }
        },

        clearLocation() {
            const locationInfo = document.getElementById('location-info');
            const clearLocationBtn = document.getElementById('clear-location-btn');
            const originAddress = document.getElementById('origin_address');

            // Clear location data
            this.currentLocation = null;
            document.getElementById('origin_latitude').value = '';
            document.getElementById('origin_longitude').value = '';
            originAddress.value = '';

            // Hide location info
            locationInfo.classList.add('hidden');
            clearLocationBtn.classList.add('hidden');
        },

        previewLocation() {
            if (!this.currentLocation) return;

            const lat = this.currentLocation.lat;
            const lng = this.currentLocation.lng;

            // Open location in new tab with maps
            const mapsUrl = `https://www.google.com/maps?q=${lat},${lng}`;
            window.open(mapsUrl, '_blank');
        },

        showLocationError(message) {
            const statusDiv = document.getElementById('location-status');
            const getLocationBtn = document.getElementById('get-location-btn');

            statusDiv.classList.add('hidden');
            getLocationBtn.disabled = false;
            getLocationBtn.innerHTML = '<i class="fas fa-map-marker-alt mr-2"></i>Dapatkan Lokasi Saya';

            alert(message);
        },

        toggleShippingMethod() {
            const checked = document.querySelector('input[name="shipping_method"]:checked');
            const method = checked ? checked.value : (this.shippingMethod || 'manual');
            this.shippingMethod = method;

            if (method === 'rajaongkir') {
                document.getElementById('rajaongkir_fields').classList.remove('hidden');
                document.getElementById('manual_courier_fields').classList.add('hidden');
                this.loadProvinces();
                // Reset courier bank info for RajaOngkir
                this.selectedCourierBankInfo = null;
            } else if (method === 'manual') {
                document.getElementById('rajaongkir_fields').classList.add('hidden');
                document.getElementById('manual_courier_fields').classList.remove('hidden');
                this.loadAvailableCouriers();
            } else {
                document.getElementById('rajaongkir_fields').classList.add('hidden');
                document.getElementById('manual_courier_fields').classList.add('hidden');
            }

            this.updateCostSummary();
        },

        togglePaymentMethod() {
            const checked = document.querySelector('input[name="payment_method"]:checked');
            const method = checked ? checked.value : (this.paymentMethod || 'cod');
            this.paymentMethod = method;

            // Reset courier bank info when switching to COD
            if (method === 'cod') {
                this.selectedCourierBankInfo = null;
            }

            this.updateCostSummary();
        },

        selectAddress() {
            const select = document.getElementById('destination_address_id');
            const manualInput = document.getElementById('manual_address_input');

            if (select.value) {
                manualInput.classList.add('hidden');
                this.selectedAddress = select.options[select.selectedIndex].dataset;
            } else {
                manualInput.classList.remove('hidden');
                this.selectedAddress = null;
            }

            if (this.shippingMethod === 'rajaongkir') {
                this.calculateShipping();
            }
        },

        loadProvinces() {
            fetch('/provinces')
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('origin_province');
                    select.innerHTML = '<option value="">Pilih Provinsi</option>';

                    data.data.forEach(province => {
                        const option = document.createElement('option');
                        option.value = province.id;
                        option.textContent = province.name;
                        select.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading provinces:', error));
        },

        loadOriginCities() {
            const provinceId = document.getElementById('origin_province').value;
            const citySelect = document.getElementById('origin_city');

            citySelect.innerHTML = '<option value="">Pilih Kota</option>';

            if (!provinceId) return;

            fetch(`/cities?province_id=${provinceId}`)
                .then(response => response.json())
                .then(data => {
                    data.data.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city.id;
                        option.textContent = `${city.name} (${city.type})`;
                        citySelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading cities:', error));
        },

        loadAvailableCouriers() {
            const loadingDiv = document.getElementById('courier_loading');
            const courierList = document.getElementById('courier_list');
            const noCouriers = document.getElementById('no_couriers');

            // Show loading
            loadingDiv.classList.remove('hidden');
            courierList.classList.add('hidden');
            noCouriers.classList.add('hidden');

                        fetch('/available-couriers')
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        // Try to extract JSON from response if it's mixed with other output
                        return response.text().then(text => {
                            const jsonMatch = text.match(/\{[\s\S]*\}/);
                            if (jsonMatch) {
                                try {
                                    return JSON.parse(jsonMatch[0]);
                                } catch (e) {
                                    throw new Error('Invalid JSON in response: ' + text.substring(0, 200));
                                }
                            }
                            throw new Error('Response is not JSON: ' + text.substring(0, 200));
                        });
                    }

                    return response.text().then(text => {
                        // Even if content-type is JSON, try to extract JSON if response is contaminated
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            // If parsing fails, try to extract JSON from text
                            const jsonMatch = text.match(/\{[\s\S]*\}/);
                            if (jsonMatch) {
                                try {
                                    return JSON.parse(jsonMatch[0]);
                                } catch (e2) {
                                    throw new Error('Invalid JSON in response: ' + text.substring(0, 200));
                                }
                            }
                            throw new Error('Invalid JSON response: ' + text.substring(0, 200));
                        }
                    });
                })
                .then(data => {
                    console.log('Courier data received:', data);
                    loadingDiv.classList.add('hidden');

                    if (data.success && data.data && data.data.length > 0) {
                        courierList.classList.remove('hidden');
                        this.displayCourierOptions(data.data);
                    } else {
                        noCouriers.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error loading couriers:', error);
                    loadingDiv.classList.add('hidden');
                    noCouriers.classList.remove('hidden');

                    // Show error message to user
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'text-center py-4 text-red-600';
                    errorMessage.innerHTML = `<p class="text-sm">Error: ${error.message}</p>`;
                    noCouriers.appendChild(errorMessage);
                });
        },

        displayCourierOptions(couriers) {
            const courierList = document.getElementById('courier_list');
            const itemWeight = parseFloat(document.getElementById('item_weight').value) || 0;

            courierList.innerHTML = couriers.map((courier, index) => {
                const totalFee = courier.base_fee + (courier.per_kg_fee * itemWeight);
                const isSelected = index === 0; // Select first courier by default

                if (isSelected) {
                    // Set the selected courier data
                    document.getElementById('selected_courier_id').value = courier.id;
                    document.getElementById('selected_courier_pricing_id').value = courier.pricing_id;
                    // Set bank info for first courier
                    this.selectedCourierBankInfo = courier.bank_info || null;
                }

                return `
                    <label class="relative flex cursor-pointer rounded-lg border ${isSelected ? 'border-green-500 bg-green-50' : 'border-gray-300 bg-white'} p-4 shadow-sm focus:outline-none">
                        <input type="radio" name="selected_courier" value="${courier.id}"
                               data-pricing-id="${courier.pricing_id}"
                               data-base-fee="${courier.base_fee}"
                               data-per-kg-fee="${courier.per_kg_fee}"
                               data-bank-info='${JSON.stringify(courier.bank_info || null)}'
                               ${isSelected ? 'checked' : ''}
                               @change="selectCourier()" class="sr-only">
                        <span class="flex flex-1">
                            <span class="flex flex-col">
                                <span class="block text-sm font-medium text-gray-900">${courier.name}</span>
                                <span class="mt-1 flex items-center text-sm text-gray-500">
                                    <i class="fas fa-phone mr-1"></i>${courier.phone || 'Tidak ada nomor telepon'}
                                </span>
                                <span class="mt-1 text-xs text-gray-400">
                                    Biaya dasar: ${this.formatRupiah(courier.base_fee)} + ${this.formatRupiah(courier.per_kg_fee)}/kg
                                </span>
                            </span>
                        </span>
                        <span class="flex flex-col items-end">
                            <span class="text-sm font-medium text-gray-900 courier-total-fee">${this.formatRupiah(totalFee)}</span>
                            ${isSelected ? '<span class="text-xs text-green-600">Terpilih</span>' : ''}
                        </span>
                    </label>
                `;
            }).join('');

            // Update cost summary after displaying couriers
            this.updateCostSummary();
        },

                selectCourier() {
            const selectedCourier = document.querySelector('input[name="selected_courier"]:checked');
            if (selectedCourier) {
                document.getElementById('selected_courier_id').value = selectedCourier.value;
                document.getElementById('selected_courier_pricing_id').value = selectedCourier.dataset.pricingId;

                // Update bank info for selected courier
                try {
                    this.selectedCourierBankInfo = JSON.parse(selectedCourier.dataset.bankInfo);
                } catch (e) {
                    this.selectedCourierBankInfo = null;
                }

                // Update visual selection
                document.querySelectorAll('input[name="selected_courier"]').forEach((radio, index) => {
                    const label = radio.closest('label');
                    if (radio.checked) {
                        label.classList.remove('border-gray-300', 'bg-white');
                        label.classList.add('border-green-500', 'bg-green-50');
                        const tag = label.querySelector('.text-xs');
                        if (tag) tag.textContent = 'Terpilih';
                    } else {
                        label.classList.remove('border-green-500', 'bg-green-50');
                        label.classList.add('border-gray-300', 'bg-white');
                        const tag = label.querySelector('.text-xs');
                        if (tag) tag.textContent = '';
                    }
                });

                this.updateCostSummary();
            }
        },

        updateCourierFees() {
            if (this.shippingMethod === 'manual') {
                const itemWeight = parseFloat(document.getElementById('item_weight').value) || 0;

                // Update courier fee displays
                document.querySelectorAll('input[name="selected_courier"]').forEach(radio => {
                    const label = radio.closest('label');
                    const baseFee = parseFloat(radio.dataset.baseFee) || 0;
                    const perKgFee = parseFloat(radio.dataset.perKgFee) || 0;
                    const totalFee = baseFee + (perKgFee * itemWeight);

                    // Update the fee display
                    const feeElement = label.querySelector('.courier-total-fee');
                    if (feeElement) {
                        feeElement.textContent = this.formatRupiah(totalFee);
                    }
                });

                // Update cost summary
                this.updateCostSummary();
            }
        },

        formatRupiah(value) {
            const number = Number(value) || 0;
            return `Rp${number.toLocaleString('id-ID')}`;
        },

        calculateShipping() {
            console.log('calculateShipping called');

            if (this.shippingMethod !== 'rajaongkir' || !this.selectedAddress) {
                console.log('Not RajaOngkir or no address selected, updating cost summary');
                this.updateCostSummary();
                return;
            }

            const weight = document.getElementById('item_weight').value;
            const originCity = document.getElementById('origin_city').value;
            const destinationCity = this.selectedAddress.city;
            const courierService = document.getElementById('courier_select').value;

            console.log('Shipping calculation data:', {
                weight,
                originCity,
                destinationCity,
                selectedAddress: this.selectedAddress,
                courierService: courierService
            });

            if (!weight || !originCity || !destinationCity || !courierService) {
                console.log('Missing required data for shipping calculation');
                this.updateCostSummary();
                return;
            }

            console.log('Making API call to calculate shipping...');

            // Get CSRF token (guard if missing)
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
            console.log('CSRF Token:', csrfToken);

            fetch('/api/calculate-shipping', {
                method: 'POST',
                headers: Object.assign({
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }, csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                body: JSON.stringify({
                    origin: originCity,
                    destination: destinationCity,
                    weight: weight,
                    courier: courierService
                })
            })
            .then(response => {
                console.log('API response status:', response.status);
                console.log('API response headers:', response.headers);

                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    console.error('Response is not JSON:', contentType);
                    throw new Error('Response is not JSON: ' + contentType);
                }

                return response.json();
            })
            .then(data => {
                console.log('API response data:', data);
                if (data.success) {
                    // Treat all successful responses as valid RajaOngkir data
                    this.updateCostSummary(data.data);
                } else {
                    console.error('API call failed:', data.message);
                    alert('Gagal menghitung ongkir: ' + (data.message || 'Unknown error'));
                    this.updateCostSummary();
                }
            })
            .catch(error => {
                console.error('Error calculating shipping:', error);

                // Show more specific error messages
                if (error.message.includes('Response is not JSON')) {
                    alert('Server error: Response tidak valid. Silakan coba lagi.');
                } else if (error.message.includes('Failed to fetch')) {
                    alert('Koneksi error: Tidak dapat terhubung ke server. Periksa koneksi internet Anda.');
                } else {
                    alert('Gagal menghitung ongkir: ' + error.message + '\nSilakan coba lagi atau pilih metode pengiriman manual.');
                }

                this.updateCostSummary();
            });
        },

        updateCostSummary(shippingData = null) {
            const itemPrice = parseFloat(document.getElementById('item_price').value) || 0;
            const itemWeight = parseFloat(document.getElementById('item_weight').value) || 0;

            let shippingCost = 0;
            let serviceFee = 0;
            let courierService = '';
            let courierCost = 0;
            let courierEtd = '';

            if (this.shippingMethod === 'manual') {
                // Get selected courier for manual delivery
                const selectedCourier = document.querySelector('input[name="selected_courier"]:checked');

                if (selectedCourier) {
                    const baseFee = parseFloat(selectedCourier.dataset.baseFee) || 0;
                    const perKgFee = parseFloat(selectedCourier.dataset.perKgFee) || 0;
                    shippingCost = baseFee + (perKgFee * itemWeight);
                    serviceFee = 5000; // Reduced service fee for manual delivery
                    courierService = 'manual';
                    courierCost = shippingCost;
                    courierEtd = '1-3 hari';

                    // Clear courier_service for manual delivery
                    document.getElementById('selected_courier_service').value = 'manual';
                } else {
                    // Fallback to old calculation if no courier selected
                    if (itemWeight <= 1) {
                        shippingCost = 15000;
                    } else if (itemWeight <= 5) {
                        shippingCost = 25000;
                    } else if (itemWeight <= 10) {
                        shippingCost = 40000;
                    } else {
                        shippingCost = 40000 + (Math.ceil(itemWeight - 10) * 3000);
                    }
                    serviceFee = 5000;
                    courierService = 'manual';
                }
            } else if (shippingData && shippingData.length > 0) {
                // Display all available options and use the cheapest
                this.displayShippingOptions(shippingData);

                // Use the cheapest option from RajaOngkir
                const cheapest = shippingData.reduce((min, current) =>
                    current.cost < min.cost ? current : min
                );
                shippingCost = cheapest.cost;
                serviceFee = 3000;
                courierService = cheapest.service || document.getElementById('courier_select').value;
                courierCost = cheapest.cost;
                courierEtd = cheapest.etd || '1-2 hari';

                console.log('Selected cheapest option:', cheapest);
            } else if (this.shippingMethod === 'rajaongkir') {
                // For RajaOngkir without shipping data, use selected courier
                const selectedCourier = document.getElementById('courier_select').value;
                if (selectedCourier) {
                    courierService = selectedCourier;
                    // Set default values if no shipping data
                    shippingCost = 0;
                    serviceFee = 3000;
                    courierCost = 0;
                    courierEtd = '1-2 hari';

                    // Set courier_service for RajaOngkir
                    document.getElementById('selected_courier_service').value = selectedCourier;
                }
            }

            const total = itemPrice + shippingCost + serviceFee;

            // Update display
            document.getElementById('item_cost').textContent = `Rp ${itemPrice.toLocaleString('id-ID')}`;
            document.getElementById('shipping_cost').textContent = `Rp ${shippingCost.toLocaleString('id-ID')}`;
            document.getElementById('service_fee').textContent = `Rp ${serviceFee.toLocaleString('id-ID')}`;
            document.getElementById('total_cost').textContent = `Rp ${total.toLocaleString('id-ID')}`;

            // Update hidden fields for form submission
            document.getElementById('calculated_shipping_cost').value = shippingCost;
            document.getElementById('calculated_service_fee').value = serviceFee;
            document.getElementById('calculated_total_cost').value = total;
            document.getElementById('selected_courier_service').value = courierService;
            document.getElementById('selected_courier_cost').value = courierCost;
            document.getElementById('selected_courier_etd').value = courierEtd;

            // Debug: Log the values being set
            console.log('Setting hidden fields:', {
                shippingCost,
                serviceFee,
                total,
                courierService,
                courierCost,
                courierEtd,
                shippingMethod: this.shippingMethod
            });

            // Clear courier_id and courier_pricing_id for RajaOngkir
            if (this.shippingMethod === 'rajaongkir') {
                document.getElementById('selected_courier_id').value = '';
                document.getElementById('selected_courier_pricing_id').value = '';
            }

            // Show/hide cost summary
            if (itemPrice > 0 || shippingCost > 0) {
                document.getElementById('cost_summary').classList.remove('hidden');
            } else {
                document.getElementById('cost_summary').classList.add('hidden');
            }
        },

        displayShippingOptions(shippingData) {
            // Create shipping options display
            const costSummary = document.getElementById('cost_summary');
            const existingOptions = costSummary.querySelector('.shipping-options');
            if (existingOptions) {
                existingOptions.remove();
            }

            if (shippingData.length > 1) {
                const optionsDiv = document.createElement('div');
                optionsDiv.className = 'shipping-options mt-4 p-3 bg-white border border-gray-200 rounded-lg';
                optionsDiv.innerHTML = `
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Pilihan Pengiriman:</h4>
                    <div class="space-y-2">
                        ${shippingData.map((option, index) => `
                            <div class="flex items-center justify-between p-2 border border-gray-200 rounded ${index === 0 ? 'bg-blue-50 border-blue-300' : ''}">
                                <div>
                                    <div class="font-medium text-sm">${option.description}</div>
                                    <div class="text-xs text-gray-500">${option.service.toUpperCase()} - ${option.etd}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-medium text-sm">Rp ${option.cost.toLocaleString('id-ID')}</div>
                                    ${index === 0 ? '<div class="text-xs text-blue-600">Terpilih</div>' : ''}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
                costSummary.appendChild(optionsDiv);
            }
        }
    }
}

// Form validation function
function validateForm() {
    console.log('Validating form...');

    const itemDescription = document.getElementById('item_description').value.trim();
    const itemWeight = document.getElementById('item_weight').value;
    const itemPrice = document.getElementById('item_price').value;
    const shippingMethod = document.querySelector('input[name="shipping_method"]:checked');
    const originAddress = document.getElementById('origin_address').value.trim();
    const destinationAddressId = document.getElementById('destination_address_id').value;
    const destinationAddress = document.getElementById('destination_address').value.trim();
    const courierService = document.getElementById('courier_select').value;

    // Debug: Log form data
    console.log('Form data:', {
        itemDescription,
        itemWeight,
        itemPrice,
        shippingMethod: shippingMethod ? shippingMethod.value : null,
        originAddress,
        destinationAddressId,
        destinationAddress,
        courierService: courierService,
        shippingCost: document.getElementById('calculated_shipping_cost').value,
        serviceFee: document.getElementById('calculated_service_fee').value,
        totalCost: document.getElementById('calculated_total_cost').value,
        courierService: document.getElementById('selected_courier_service').value,
    });

    // Check required fields
    if (!itemDescription) {
        alert('Deskripsi barang harus diisi');
        document.getElementById('item_description').focus();
        return false;
    }

    if (!itemWeight || itemWeight <= 0) {
        alert('Berat barang harus diisi dan lebih dari 0');
        document.getElementById('item_weight').focus();
        return false;
    }

    if (!itemPrice || itemPrice <= 0) {
        alert('Nilai barang harus diisi dan lebih dari 0');
        document.getElementById('item_price').focus();
        return false;
    }

    if (!shippingMethod) {
        alert('Pilih metode pengiriman');
        return false;
    }

    if (!originAddress) {
        alert('Alamat asal harus diisi');
        document.getElementById('origin_address').focus();
        return false;
    }

    if (!destinationAddressId && !destinationAddress) {
        alert('Pilih alamat tujuan atau isi alamat tujuan manual');
        return false;
    }

    if (shippingMethod.value === 'rajaongkir') {
        const originProvince = document.getElementById('origin_province').value;
        const originCity = document.getElementById('origin_city').value;

        if (!originProvince) {
            alert('Pilih provinsi asal untuk pengiriman RajaOngkir');
            document.getElementById('origin_province').focus();
            return false;
        }

        if (!originCity) {
            alert('Pilih kota asal untuk pengiriman RajaOngkir');
            document.getElementById('origin_city').focus();
            return false;
        }

        if (!destinationAddressId) {
            alert('Pilih alamat tujuan yang memiliki data kota untuk pengiriman RajaOngkir');
            return false;
        }

        if (!courierService) {
            alert('Pilih kurir untuk pengiriman RajaOngkir');
            document.getElementById('courier_select').focus();
            return false;
        }
    } else if (shippingMethod.value === 'manual') {
        const selectedCourier = document.querySelector('input[name="selected_courier"]:checked');
        if (!selectedCourier) {
            alert('Pilih kurir untuk pengiriman manual');
            return false;
        }
    }

    // Check if cost has been calculated
    const totalCost = document.getElementById('calculated_total_cost').value;
    if (!totalCost || totalCost <= 0) {
        alert('Silakan tunggu perhitungan biaya selesai atau isi ulang form');
        return false;
    }

    console.log('Form validation passed!');
    return true;
}
</script>
@endpush
@endsection
