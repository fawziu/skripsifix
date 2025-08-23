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
        <form method="POST" action="{{ route('orders.store') }}" x-data="orderForm()" class="p-6">
            @csrf

            <div class="space-y-6">
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
                                <label class="relative flex cursor-pointer rounded-lg border border-gray-300 bg-white p-4 shadow-sm focus:outline-none">
                                    <input type="radio" name="shipping_method" value="manual" @change="toggleShippingMethod()" class="sr-only" required>
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <span class="block text-sm font-medium text-gray-900">Manual</span>
                                            <span class="mt-1 flex items-center text-sm text-gray-500">
                                                Pengiriman manual dengan biaya tetap
                                            </span>
                                        </span>
                                    </span>
                                </label>
                                <label class="relative flex cursor-pointer rounded-lg border border-gray-300 bg-white p-4 shadow-sm focus:outline-none">
                                    <input type="radio" name="shipping_method" value="rajaongkir" @change="toggleShippingMethod()" class="sr-only" required>
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <span class="block text-sm font-medium text-gray-900">RajaOngkir</span>
                                            <span class="mt-1 flex items-center text-sm text-gray-500">
                                                Pengiriman dengan RajaOngkir
                                            </span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                            @error('shipping_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Origin Address -->
                        <div class="md:col-span-2">
                            <label for="origin_address" class="block text-sm font-medium text-gray-700 mb-1">
                                Alamat Asal <span class="text-red-500">*</span>
                            </label>
                            <textarea id="origin_address" name="origin_address" rows="3" required
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('origin_address') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                      placeholder="Masukkan alamat asal pengiriman">{{ old('origin_address') }}</textarea>
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
                    </div>
                </div>

                <!-- Hidden Fields for Form Submission -->
                <input type="hidden" id="calculated_shipping_cost" name="shipping_cost" value="0">
                <input type="hidden" id="calculated_service_fee" name="service_fee" value="0">
                <input type="hidden" id="calculated_total_cost" name="total_cost" value="0">
                <input type="hidden" id="selected_courier_service" name="courier_service" value="">
                <input type="hidden" id="selected_courier_cost" name="courier_cost" value="">
                <input type="hidden" id="selected_courier_etd" name="courier_etd" value="">

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

        init() {
            this.updateCostSummary();
        },

        toggleShippingMethod() {
            const method = document.querySelector('input[name="shipping_method"]:checked').value;
            this.shippingMethod = method;

            if (method === 'rajaongkir') {
                document.getElementById('rajaongkir_fields').classList.remove('hidden');
                this.loadProvinces();
            } else {
                document.getElementById('rajaongkir_fields').classList.add('hidden');
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

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            console.log('CSRF Token:', csrfToken);

            fetch('/api/calculate-shipping', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
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
                // Manual shipping cost calculation
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
            }

            const total = itemPrice + shippingCost + serviceFee;

            // Update display
            document.getElementById('item_cost').textContent = `Rp ${itemPrice.toLocaleString()}`;
            document.getElementById('shipping_cost').textContent = `Rp ${shippingCost.toLocaleString()}`;
            document.getElementById('service_fee').textContent = `Rp ${serviceFee.toLocaleString()}`;
            document.getElementById('total_cost').textContent = `Rp ${total.toLocaleString()}`;

            // Update hidden fields for form submission
            document.getElementById('calculated_shipping_cost').value = shippingCost;
            document.getElementById('calculated_service_fee').value = serviceFee;
            document.getElementById('calculated_total_cost').value = total;
            document.getElementById('selected_courier_service').value = courierService;
            document.getElementById('selected_courier_cost').value = courierCost;
            document.getElementById('selected_courier_etd').value = courierEtd;

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
                                    <div class="font-medium text-sm">Rp ${option.cost.toLocaleString()}</div>
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
