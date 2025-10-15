@extends('layouts.app')

@section('title', 'Edit Alamat - Afiyah')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Alamat</h1>
                <p class="mt-2 text-gray-600">Perbarui informasi alamat Anda</p>
            </div>
            <a href="{{ route('addresses.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Address Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form method="POST" action="{{ route('addresses.update', $address) }}" x-data="addressForm()" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <!-- Address Type and Label -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                            Tipe Alamat <span class="text-red-500">*</span>
                        </label>
                        <select id="type" name="type" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                            <option value="home" {{ old('type', $address->type) == 'home' ? 'selected' : '' }}>Rumah</option>
                            <option value="office" {{ old('type', $address->type) == 'office' ? 'selected' : '' }}>Kantor</option>
                            <option value="warehouse" {{ old('type', $address->type) == 'warehouse' ? 'selected' : '' }}>Gudang</option>
                            <option value="other" {{ old('type', $address->type) == 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="label" class="block text-sm font-medium text-gray-700 mb-1">
                            Label Alamat <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="label" name="label" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('label') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                               placeholder="Contoh: Rumah Utama, Kantor Pusat"
                               value="{{ old('label', $address->label) }}">
                        @error('label')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Recipient Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="recipient_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Penerima <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="recipient_name" name="recipient_name" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('recipient_name') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                               placeholder="Nama lengkap penerima"
                               value="{{ old('recipient_name', $address->recipient_name) }}">
                        @error('recipient_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Nomor Telepon <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" id="phone" name="phone" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                               placeholder="081234567890"
                               value="{{ old('phone', $address->phone) }}">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Location Selection -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="province_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Provinsi <span class="text-red-500">*</span>
                        </label>
                        <select id="province_id" name="province_id" @change="loadCities()" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('province_id') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                            <option value="">Pilih Provinsi</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province['id'] }}" 
                                        {{ old('province_id', $address->province_id) == $province['id'] ? 'selected' : '' }}>
                                    {{ $province['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('province_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="city_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Kota/Kabupaten <span class="text-red-500">*</span>
                        </label>
                        <select id="city_id" name="city_id" @change="loadDistricts()" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('city_id') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                            <option value="">Pilih Kota</option>
                            @foreach($cities as $city)
                                <option value="{{ $city['id'] }}" 
                                        {{ old('city_id', $address->city_id) == $city['id'] ? 'selected' : '' }}>
                                    {{ $city['name'] }} ({{ $city['type'] }})
                                </option>
                            @endforeach
                        </select>
                        @error('city_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="district_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Kecamatan
                        </label>
                        <select id="district_id" name="district_id"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('district_id') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                            <option value="">Pilih Kecamatan</option>
                            @foreach($districts as $district)
                                <option value="{{ $district['id'] }}" 
                                        {{ old('district_id', $address->district_id) == $district['id'] ? 'selected' : '' }}>
                                    {{ $district['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('district_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Map Location -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Lokasi di Peta
                    </label>
                    <div id="map" class="h-96 w-full rounded-lg border border-gray-300 mb-2"></div>
                    <div class="flex items-center space-x-4">
                        <button type="button" onclick="getCurrentLocation()"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-location-arrow mr-2"></i>Gunakan Lokasi Saat Ini
                        </button>
                        <div id="locationLoading" class="hidden items-center text-sm text-gray-600">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Mendapatkan detail lokasi...
                        </div>
                    </div>

                    <!-- Hidden coordinate inputs -->
                    <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $address->latitude) }}">
                    <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $address->longitude) }}">
                    <input type="hidden" id="accuracy" name="accuracy" value="{{ old('accuracy', $address->accuracy) }}">
                </div>

                <!-- Address Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">
                            Kode Pos
                        </label>
                        <input type="text" id="postal_code" name="postal_code"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('postal_code') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                               placeholder="12345"
                               value="{{ old('postal_code', $address->postal_code) }}">
                        @error('postal_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_primary" name="is_primary" value="1"
                               {{ old('is_primary', $address->is_primary) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_primary" class="ml-2 block text-sm text-gray-900">
                            Jadikan alamat utama
                        </label>
                    </div>
                </div>

                <div>
                    <label for="address_line" class="block text-sm font-medium text-gray-700 mb-1">
                        Alamat Lengkap <span class="text-red-500">*</span>
                    </label>
                    <textarea id="address_line" name="address_line" rows="3" required
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('address_line') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                              placeholder="Masukkan alamat lengkap (nama jalan, nomor rumah, dll)">{{ old('address_line', $address->address_line) }}</textarea>
                    @error('address_line')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-4 pt-6">
                    <a href="{{ route('addresses.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<!-- Leaflet CSS & JS for map picker -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="{{ asset('js/address-map.js') }}"></script>
<script>
// Initialize map with existing coordinates if available
document.addEventListener('DOMContentLoaded', function() {
    const lat = parseFloat(document.getElementById('latitude').value);
    const lng = parseFloat(document.getElementById('longitude').value);
    if (!isNaN(lat) && !isNaN(lng)) {
        initMap('map', lat, lng);
    } else {
        initMap('map');
    }
});

function addressForm() {
    return {
        loadCities() {
            const provinceId = document.getElementById('province_id').value;
            const citySelect = document.getElementById('city_id');
            const districtSelect = document.getElementById('district_id');
            
            citySelect.innerHTML = '<option value="">Pilih Kota</option>';
            districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            
            if (!provinceId) return;
            
            fetch(`/addresses/cities?province_id=${provinceId}`)
                .then(response => response.json())
                .then(cities => {
                    cities.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city.id;
                        option.textContent = `${city.name} (${city.type})`;
                        citySelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading cities:', error));
        },
        
        loadDistricts() {
            const cityId = document.getElementById('city_id').value;
            const districtSelect = document.getElementById('district_id');
            
            districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            
            if (!cityId) return;
            
            fetch(`/addresses/districts?city_id=${cityId}`)
                .then(response => response.json())
                .then(districts => {
                    districts.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.id;
                        option.textContent = district.name;
                        districtSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading districts:', error));
        }
    }
}
</script>
@endpush
@endsection
