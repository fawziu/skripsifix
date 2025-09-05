@extends('layouts.app')

@section('title', 'Kelola Harga Pengiriman')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-dollar-sign mr-2 text-blue-600"></i>
                Kelola Harga Pengiriman
            </h3>
        </div>
        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle mr-2 mt-1"></i>
                        <div>
                            <strong>Terjadi kesalahan:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h5 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-edit mr-2 text-blue-600"></i>
                                {{ $pricing ? 'Update Harga Pengiriman' : 'Atur Harga Pengiriman' }}
                            </h5>
                        </div>
                        <div class="p-6">
                                    <form action="{{ $pricing ? route('courier.pricing.update') : route('courier.pricing.store') }}" method="POST" id="pricingForm">
                                        @csrf
                                        @if($pricing)
                                            @method('PUT')
                                        @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="base_fee" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-coins mr-1 text-yellow-600"></i>
                                        Biaya Dasar (Rp)
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number"
                                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('base_fee') border-red-500 @enderror"
                                               id="base_fee"
                                               name="base_fee"
                                               value="{{ old('base_fee', $pricing->base_fee ?? '') }}"
                                               min="0"
                                               step="100"
                                               required>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Biaya minimum untuk setiap pengiriman
                                    </p>
                                    @error('base_fee')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="per_kg_fee" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-weight mr-1 text-green-600"></i>
                                        Biaya per Kg (Rp)
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number"
                                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('per_kg_fee') border-red-500 @enderror"
                                               id="per_kg_fee"
                                               name="per_kg_fee"
                                               value="{{ old('per_kg_fee', $pricing->per_kg_fee ?? '') }}"
                                               min="0"
                                               step="100"
                                               required>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Biaya tambahan untuk setiap kilogram
                                    </p>
                                    @error('per_kg_fee')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-6">
                                <div class="flex items-center">
                                    <input type="checkbox"
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded @error('is_active') border-red-500 @enderror"
                                           id="is_active"
                                           name="is_active"
                                           value="1"
                                           {{ old('is_active', $pricing->is_active ?? true) ? 'checked' : '' }}>
                                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                        <i class="fas fa-toggle-on mr-1 text-green-600"></i>
                                        Aktifkan harga pengiriman
                                    </label>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    Jika tidak aktif, harga tidak akan muncul dalam pilihan pengiriman
                                </p>
                                @error('is_active')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mt-6 flex space-x-4">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <i class="fas fa-save mr-2"></i>
                                    {{ $pricing ? 'Update Harga' : 'Simpan Harga' }}
                                </button>
                                <a href="{{ route('courier.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Kembali ke Dashboard
                                </a>
                            </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h5 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-calculator mr-2 text-blue-600"></i>
                                Kalkulator Harga
                            </h5>
                        </div>
                        <div class="p-6">
                            <div class="mb-4">
                                <label for="test_weight" class="block text-sm font-medium text-gray-700 mb-2">Berat Paket (kg)</label>
                                <input type="number"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       id="test_weight"
                                       min="0.1"
                                       step="0.1"
                                       value="1">
                            </div>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <strong class="text-blue-900">Estimasi Harga:</strong>
                                <div id="estimated_price" class="mt-2">
                                    <span class="text-2xl font-bold text-blue-600">Rp 0</span>
                                </div>
                            </div>
                            <p class="mt-3 text-sm text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Harga akan dihitung otomatis berdasarkan pengaturan di atas
                            </p>
                        </div>
                    </div>

                    @if($pricing)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h5 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-chart-line mr-2 text-green-600"></i>
                                Statistik Harga
                            </h5>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-4 text-center">
                                <div class="border-r border-gray-200 pr-4">
                                    <h4 class="text-2xl font-bold text-blue-600">{{ number_format($pricing->base_fee, 0, ',', '.') }}</h4>
                                    <p class="text-sm text-gray-500">Biaya Dasar</p>
                                </div>
                                <div>
                                    <h4 class="text-2xl font-bold text-green-600">{{ number_format($pricing->per_kg_fee, 0, ',', '.') }}</h4>
                                    <p class="text-sm text-gray-500">Per Kg</p>
                                </div>
                            </div>
                            <hr class="my-4">
                            <div class="text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $pricing->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <i class="fas fa-{{ $pricing->is_active ? 'check' : 'times' }} mr-1"></i>
                                    {{ $pricing->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 text-center mt-3">
                                Terakhir diupdate: {{ $pricing->updated_at->format('d M Y H:i') }}
                            </p>
                        </div>
                    </div>
                    @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real-time price calculation
    function calculatePrice() {
        const baseFee = parseFloat(document.getElementById('base_fee').value) || 0;
        const perKgFee = parseFloat(document.getElementById('per_kg_fee').value) || 0;
        const weight = parseFloat(document.getElementById('test_weight').value) || 0;

        const totalPrice = baseFee + (perKgFee * weight);

        document.getElementById('estimated_price').innerHTML = '<span class="text-2xl font-bold text-blue-600">Rp ' + totalPrice.toLocaleString('id-ID') + '</span>';
    }

    // Calculate on input change
    const inputs = ['base_fee', 'per_kg_fee', 'test_weight'];
    inputs.forEach(function(inputId) {
        const element = document.getElementById(inputId);
        if (element) {
            element.addEventListener('input', calculatePrice);
        }
    });

    // Initial calculation
    calculatePrice();

    // Form validation
    const form = document.getElementById('pricingForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const baseFee = parseFloat(document.getElementById('base_fee').value);
            const perKgFee = parseFloat(document.getElementById('per_kg_fee').value);

            if (baseFee < 0 || perKgFee < 0) {
                e.preventDefault();
                alert('Harga tidak boleh negatif!');
                return false;
            }

            if (baseFee === 0 && perKgFee === 0) {
                e.preventDefault();
                alert('Minimal salah satu harga harus lebih dari 0!');
                return false;
            }
        });
    }

    // Auto-dismiss alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        });
    }, 5000);
});
</script>
@endpush

