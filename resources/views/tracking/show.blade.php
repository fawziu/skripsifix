@extends('layouts.app')

@section('title', 'Live Tracking - ' . $order->order_number)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Live Tracking</h1>
                <p class="mt-2 text-gray-600">Pesanan #{{ $order->order_number }}</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('orders.show', $order) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Detail
                </a>
                @if(Auth::user()->isCourier() || Auth::user()->isAdmin())
                <button id="start-tracking-btn" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-play mr-2"></i>
                    Mulai Tracking
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Map Container -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Peta Tracking</h3>
                
                <!-- Location Permission Alert -->
                <div id="location-permission-alert" class="hidden mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                        <div>
                            <h4 class="text-sm font-medium text-yellow-800">Izin Lokasi Diperlukan</h4>
                            <p class="text-sm text-yellow-700 mt-1">Untuk melacak lokasi secara real-time, aplikasi memerlukan izin akses lokasi Anda.</p>
                        </div>
                    </div>
                </div>

                <!-- Map -->
                <div id="map" class="w-full h-96 rounded-lg border border-gray-200"></div>
                
                <!-- Tracking Status -->
                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600">Kurir</span>
                        </div>
                        @if(Auth::user()->isCourier() || Auth::user()->isAdmin())
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600">Customer</span>
                        </div>
                        @endif
                    </div>
                    <div class="text-sm text-gray-500">
                        <span id="last-update">Belum ada data lokasi</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Order Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pesanan</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-600">No. Pesanan:</span>
                        <p class="text-sm text-gray-900">{{ $order->order_number }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Status:</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                            @elseif($order->status === 'assigned') bg-indigo-100 text-indigo-800
                            @elseif($order->status === 'picked_up') bg-purple-100 text-purple-800
                            @elseif($order->status === 'in_transit') bg-orange-100 text-orange-800
                            @elseif($order->status === 'delivered') bg-green-100 text-green-800
                            @elseif($order->status === 'awaiting_confirmation') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                        </span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Item:</span>
                        <p class="text-sm text-gray-900">{{ $order->item_description }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Tujuan:</span>
                        <p class="text-sm text-gray-900">{{ $order->destination_address }}</p>
                    </div>
                </div>
            </div>

            <!-- Courier Info -->
            @if($order->courier)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Kurir</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-600">Nama:</span>
                        <p class="text-sm text-gray-900">{{ $order->courier->name }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Telepon:</span>
                        <p class="text-sm text-gray-900">{{ $order->courier->phone }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Status Lokasi:</span>
                        <p class="text-sm text-gray-900" id="courier-location-status">Tidak tersedia</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Customer Info - Only for courier and admin -->
            @if(Auth::user()->isCourier() || Auth::user()->isAdmin())
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Customer</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-600">Nama:</span>
                        <p class="text-sm text-gray-900">{{ $order->customer->name }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Telepon:</span>
                        <p class="text-sm text-gray-900">{{ $order->customer->phone }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Status Lokasi:</span>
                        <p class="text-sm text-gray-900" id="customer-location-status">Tidak tersedia</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Tracking Controls -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Kontrol Tracking</h3>
                <div class="space-y-3">
                    <button id="refresh-locations-btn" 
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Refresh Lokasi
                    </button>
                    <button id="center-map-btn" 
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-crosshairs mr-2"></i>
                        Posisi Saya
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .leaflet-popup-content-wrapper {
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .leaflet-popup-content {
        margin: 8px 12px;
        line-height: 1.4;
    }
    .courier-marker {
        background-color: #3b82f6;
        border: 3px solid #ffffff;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.4);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .customer-marker {
        background-color: #10b981;
        border: 3px solid #ffffff;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.4);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .leaflet-marker-icon {
        border-radius: 50%;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/tracking.js') }}"></script>
@endpush
@endsection
