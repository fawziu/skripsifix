@extends('layouts.app')

@section('title', 'Track Order - Afiyah')

@section('content')
@if(!Auth::user()->isAdmin())
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span>Anda tidak memiliki akses ke halaman ini.</span>
            </div>
        </div>
        <div class="mt-4">
            <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Detail Pesanan
            </a>
        </div>
    </div>
@else
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Lacak Pesanan #{{ $order->id }}</h1>
                <p class="mt-2 text-gray-600">Informasi tracking pengiriman</p>
            </div>
            <a href="{{ route('orders.show', $order) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Detail
            </a>
        </div>
    </div>

    <!-- Order Info -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pesanan</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-600">Order Number:</span>
                <p class="text-sm text-gray-900 mt-1">{{ $order->order_number }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-600">Status:</span>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-1
                    @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                    @elseif($order->status === 'assigned') bg-indigo-100 text-indigo-800
                    @elseif($order->status === 'picked_up') bg-purple-100 text-purple-800
                    @elseif($order->status === 'in_transit') bg-orange-100 text-orange-800
                    @elseif($order->status === 'delivered') bg-green-100 text-green-800
                    @else bg-red-100 text-red-800 @endif">
                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                </span>
            </div>
            @if($order->tracking_number)
            <div>
                <span class="text-sm font-medium text-gray-600">Tracking Number:</span>
                <p class="text-sm text-gray-900 mt-1 font-mono">{{ $order->tracking_number }}</p>
            </div>
            @endif
            @if($order->courier_service)
            <div>
                <span class="text-sm font-medium text-gray-600">Courier Service:</span>
                <p class="text-sm text-gray-900 mt-1">{{ strtoupper($order->courier_service) }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Tracking Information -->
    <div id="tracking-container">
        <!-- Loading State -->
        <div id="loading-state" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="ml-3 text-gray-600">Memuat informasi tracking...</span>
            </div>
        </div>

        <!-- Error State -->
        <div id="error-state" class="hidden bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="text-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Gagal Memuat Tracking</h3>
                <p class="text-gray-600 mb-4" id="error-message">Terjadi kesalahan saat memuat informasi tracking.</p>
                <button onclick="loadTracking()"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-refresh mr-2"></i>
                    Coba Lagi
                </button>
            </div>
        </div>

        <!-- Manual Tracking Info -->
        <div id="manual-tracking" class="hidden bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="text-center">
                <i class="fas fa-info-circle text-blue-500 text-3xl mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Tracking Manual</h3>
                <p class="text-gray-600 mb-4">Pesanan ini menggunakan metode pengiriman manual. Silakan hubungi admin untuk informasi tracking.</p>
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Status Pesanan:</h4>
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full
                        @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                        @elseif($order->status === 'assigned') bg-indigo-100 text-indigo-800
                        @elseif($order->status === 'picked_up') bg-purple-100 text-purple-800
                        @elseif($order->status === 'in_transit') bg-orange-100 text-orange-800
                        @elseif($order->status === 'delivered') bg-green-100 text-green-800
                        @else bg-red-100 text-red-800 @endif">
                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- RajaOngkir Tracking Result -->
        <div id="rajaongkir-tracking" class="hidden">
            <!-- Shipment Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pengiriman</h3>
                <div id="shipment-info" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>

            <!-- Tracking Timeline -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline Pengiriman</h3>
                <div id="tracking-timeline" class="space-y-4">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadTracking();
});

function loadTracking() {
    const loadingState = document.getElementById('loading-state');
    const errorState = document.getElementById('error-state');
    const manualTracking = document.getElementById('manual-tracking');
    const rajaongkirTracking = document.getElementById('rajaongkir-tracking');

    // Show loading
    loadingState.classList.remove('hidden');
    errorState.classList.add('hidden');
    manualTracking.classList.add('hidden');
    rajaongkirTracking.classList.add('hidden');

    // Check if order supports RajaOngkir tracking
    const orderData = @json($order);

    if (orderData.shipping_method !== 'rajaongkir' || !orderData.tracking_number) {
        loadingState.classList.add('hidden');
        manualTracking.classList.remove('hidden');
        return;
    }

    // Fetch tracking data
    fetch(`/api/orders/${orderData.id}/track`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        loadingState.classList.add('hidden');

        if (data.success && data.data) {
            displayRajaOngkirTracking(data.data);
        } else {
            showError(data.message || 'Gagal memuat informasi tracking');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        loadingState.classList.add('hidden');
        showError('Terjadi kesalahan saat memuat informasi tracking');
    });
}

function displayRajaOngkirTracking(trackingData) {
    const rajaongkirTracking = document.getElementById('rajaongkir-tracking');
    const shipmentInfo = document.getElementById('shipment-info');
    const trackingTimeline = document.getElementById('tracking-timeline');

    // Display shipment info
    if (trackingData.summary) {
        const summary = trackingData.summary;
        shipmentInfo.innerHTML = `
            <div>
                <span class="text-sm font-medium text-gray-600">Status:</span>
                <p class="text-sm text-gray-900 mt-1">${summary.status || 'N/A'}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-600">Courier:</span>
                <p class="text-sm text-gray-900 mt-1">${summary.courier_name || 'N/A'}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-600">Origin:</span>
                <p class="text-sm text-gray-900 mt-1">${summary.origin || 'N/A'}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-600">Destination:</span>
                <p class="text-sm text-gray-900 mt-1">${summary.destination || 'N/A'}</p>
            </div>
        `;
    }

    // Display tracking timeline
    if (trackingData.manifest && trackingData.manifest.length > 0) {
        trackingTimeline.innerHTML = trackingData.manifest.map((item, index) => `
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-truck text-blue-600 text-sm"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">${item.manifest_description || 'Update Status'}</p>
                    <p class="text-sm text-gray-500">${item.manifest_date || 'N/A'}</p>
                    <p class="text-sm text-gray-500">${item.manifest_time || 'N/A'}</p>
                    ${item.city_name ? `<p class="text-sm text-gray-600 mt-1">${item.city_name}</p>` : ''}
                </div>
            </div>
            ${index < trackingData.manifest.length - 1 ? '<div class="ml-4 border-l-2 border-gray-200 h-4"></div>' : ''}
        `).join('');
    } else {
        trackingTimeline.innerHTML = `
            <div class="text-center text-gray-500">
                <i class="fas fa-info-circle text-2xl mb-2"></i>
                <p>Belum ada informasi tracking yang tersedia</p>
            </div>
        `;
    }

    rajaongkirTracking.classList.remove('hidden');
}

function showError(message) {
    const errorState = document.getElementById('error-state');
    const errorMessage = document.getElementById('error-message');

    errorMessage.textContent = message;
    errorState.classList.remove('hidden');
}
</script>
@endpush
@endif
@endsection
