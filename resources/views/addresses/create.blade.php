@extends('layouts.app')

@section('title', 'Tambah Alamat - Afiyah')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center">
            <a href="{{ route('addresses.index') }}" class="text-gray-600 hover:text-gray-800 mr-4">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Tambah Alamat Baru</h1>
                <p class="mt-2 text-gray-600">Tambahkan alamat pengiriman baru untuk memudahkan pemesanan</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('addresses.store') }}" x-data="addressForm()">
            @csrf
            
            <!-- Address Type and Label -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Jenis Alamat</label>
                    <select id="type" name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="home" {{ old('type') == 'home' ? 'selected' : '' }}>Rumah</option>
                        <option value="office" {{ old('type') == 'office' ? 'selected' : '' }}>Kantor</option>
                        <option value="warehouse" {{ old('type') == 'warehouse' ? 'selected' : '' }}>Gudang</option>
                        <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="label" class="block text-sm font-medium text-gray-700 mb-2">Label Alamat</label>
                    <input type="text" id="label" name="label" value="{{ old('label') }}" 
                           placeholder="Contoh: Rumah Utama, Kantor Jakarta, dll"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('label')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Recipient Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="recipient_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Penerima</label>
                    <input type="text" id="recipient_name" name="recipient_name" value="{{ old('recipient_name') }}" 
                           placeholder="Nama lengkap penerima"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('recipient_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" 
                           placeholder="08xxxxxxxxxx"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Location Selection -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="province_id" class="block text-sm font-medium text-gray-700 mb-2">Provinsi</label>
                    <select id="province_id" name="province_id" @change="loadCities()" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Provinsi</option>
                        @foreach($provinces as $province)
                            <option value="{{ $province['id'] }}" {{ old('province_id') == $province['id'] ? 'selected' : '' }}>
                                {{ $province['name'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('province_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="city_id" class="block text-sm font-medium text-gray-700 mb-2">Kota/Kabupaten</label>
                    <select id="city_id" name="city_id" @change="loadDistricts()" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Kota/Kabupaten</option>
                    </select>
                    @error('city_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="district_id" class="block text-sm font-medium text-gray-700 mb-2">Kecamatan</label>
                    <select id="district_id" name="district_id" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Kecamatan</option>
                    </select>
                    @error('district_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Address Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">Kode Pos</label>
                    <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" 
                           placeholder="12345"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('postal_code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="address_line" class="block text-sm font-medium text-gray-700 mb-2">Alamat Lengkap</label>
                    <textarea id="address_line" name="address_line" rows="3" 
                              placeholder="Contoh: Jl. Sudirman No. 123, RT 001/RW 002, Gedung ABC Lantai 5"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('address_line') }}</textarea>
                    @error('address_line')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Primary Address -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_primary" value="1" {{ old('is_primary') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Jadikan alamat utama</span>
                </label>
                <p class="mt-1 text-sm text-gray-500">Alamat utama akan menjadi default saat membuat pesanan</p>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('addresses.index') }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Alamat
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function addressForm() {
    return {
        loadCities() {
            const provinceId = document.getElementById('province_id').value;
            const citySelect = document.getElementById('city_id');
            const districtSelect = document.getElementById('district_id');
            
            // Reset city and district
            citySelect.innerHTML = '<option value="">Pilih Kota/Kabupaten</option>';
            districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            
            if (!provinceId) return;
            
            // Show loading
            citySelect.disabled = true;
            
            fetch(`/addresses/cities?province_id=${provinceId}`)
                .then(response => response.json())
                .then(cities => {
                    cities.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city.id;
                        option.textContent = `${city.name} (${city.type})`;
                        citySelect.appendChild(option);
                    });
                    citySelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error loading cities:', error);
                    citySelect.disabled = false;
                });
        },
        
        loadDistricts() {
            const cityId = document.getElementById('city_id').value;
            const districtSelect = document.getElementById('district_id');
            
            // Reset district
            districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            
            if (!cityId) return;
            
            // Show loading
            districtSelect.disabled = true;
            
            fetch(`/addresses/districts?city_id=${cityId}`)
                .then(response => response.json())
                .then(districts => {
                    districts.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.id;
                        option.textContent = district.name;
                        districtSelect.appendChild(option);
                    });
                    districtSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error loading districts:', error);
                    districtSelect.disabled = false;
                });
        }
    }
}
</script>
@endpush
@endsection
