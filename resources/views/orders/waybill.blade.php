@extends('layouts.app')

@section('title', 'Waybill & Tracking - Afiyah')

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
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 no-print">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Waybill & Tracking #{{ $order->id }}</h1>
                <p class="mt-2 text-gray-600">Informasi lengkap waybill dan tracking RajaOngkir</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('orders.show', $order) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Detail
                </a>
                @if($order->tracking_number)
                <button onclick="printWaybill()"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-print mr-2"></i>
                    Cetak Waybill
                </button>
                @if($order->status !== 'picked_up')
                    @if(isset($order->metadata['pickup_request']))
                        <button onclick="showPickupInfo()"
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-info-circle mr-2"></i>
                            Info Pickup
                        </button>
                    @else
                        <button onclick="requestPickup()"
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-truck mr-2"></i>
                            Request Pickup
                        </button>
                    @endif
                @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Order Info -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6 no-print">
        <div class="flex items-center mb-4">
            <i class="fas fa-box text-blue-500 text-xl mr-3"></i>
            <h3 class="text-lg font-semibold text-gray-900">Informasi Pesanan</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-600">Order Number:</span>
                <p class="text-sm text-gray-900 mt-1 font-mono">{{ $order->order_number }}</p>
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
            <div>
                <span class="text-sm font-medium text-gray-600">Metode Pengiriman:</span>
                <p class="text-sm text-gray-900 mt-1">
                    @if($order->shipping_method === 'rajaongkir')
                        <span class="inline-flex items-center">
                            <i class="fas fa-shipping-fast text-green-500 mr-2"></i>
                            RajaOngkir
                        </span>
                    @else
                        <span class="inline-flex items-center">
                            <i class="fas fa-truck text-blue-500 mr-2"></i>
                            Manual
                        </span>
                    @endif
                </p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-600">Customer:</span>
                <p class="text-sm text-gray-900 mt-1">{{ $order->customer->name ?? 'N/A' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-600">Courier Service:</span>
                <p class="text-sm text-gray-900 mt-1 font-medium">
                    @if($order->courier_service)
                        {{ strtoupper($order->courier_service) }}
                    @elseif($order->shipping_method === 'rajaongkir')
                        <span class="text-orange-600 italic">Belum dipilih</span>
                    @else
                        <span class="text-gray-500 italic">Manual Delivery</span>
                    @endif
                </p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-600">Total Biaya:</span>
                <p class="text-sm text-gray-900 mt-1 font-medium">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Waybill Information -->
    @if($order->tracking_number && $order->shipping_method === 'rajaongkir')
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6 no-print">
        <div class="flex items-center mb-4">
            <i class="fas fa-receipt text-green-500 text-xl mr-3"></i>
            <h3 class="text-lg font-semibold text-gray-900">Informasi Waybill</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-green-50 rounded-lg p-4">
                <h4 class="font-medium text-green-900 mb-3">Waybill Details</h4>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-green-700">Tracking Number:</span>
                        <p class="text-sm text-green-900 font-mono bg-white px-2 py-1 rounded mt-1">{{ $order->tracking_number }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-green-700">Courier:</span>
                        <p class="text-sm text-green-900">
                            @if($order->courier_service)
                                {{ strtoupper($order->courier_service) }}
                            @else
                                <span class="text-orange-600 italic">Belum dipilih</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-green-700">Status:</span>
                        <p class="text-sm text-green-900">{{ ucfirst($order->status) }}</p>
                    </div>
                    @if($order->waybill_data)
                    @php
                        $waybillData = json_decode($order->waybill_data, true);
                    @endphp
                    <div>
                        <span class="text-sm font-medium text-green-700">ETD:</span>
                        <p class="text-sm text-green-900">{{ $waybillData['etd'] ?? '1-2 hari' }}</p>
                    </div>
                    @endif
                </div>
            </div>
            <div class="bg-blue-50 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-3">Shipping Details</h4>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-blue-700">Origin:</span>
                        <p class="text-sm text-blue-900">
                            @if($order->originCity && $order->originCity->province)
                                {{ $order->originCity->name }}, {{ $order->originCity->province->name }}
                            @elseif($order->originCity)
                                {{ $order->originCity->name }}
                            @elseif($order->origin_address)
                                {{ $order->origin_address }}
                            @else
                                <span class="text-orange-600 italic">Address not specified</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-blue-700">Destination:</span>
                        <p class="text-sm text-blue-900">
                            @if($order->destinationCity && $order->destinationCity->province)
                                {{ $order->destinationCity->name }}, {{ $order->destinationCity->province->name }}
                            @elseif($order->destinationCity)
                                {{ $order->destinationCity->name }}
                            @elseif($order->destination_address)
                                {{ $order->destination_address }}
                            @else
                                <span class="text-orange-600 italic">Address not specified</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-blue-700">Weight:</span>
                        <p class="text-sm text-blue-900">{{ $order->item_weight }} kg</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-blue-700">Item:</span>
                        <p class="text-sm text-blue-900">{{ Str::limit($order->item_description ?? 'Item description not available', 50) }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-blue-700">Shipping Method:</span>
                        <p class="text-sm text-blue-900">
                            @if($order->shipping_method === 'rajaongkir')
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    <i class="fas fa-shipping-fast mr-1"></i>
                                    RajaOngkir
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    <i class="fas fa-truck mr-1"></i>
                                    Manual Delivery
                                </span>
                            @endif
                        </p>
                    </div>
                    @if($order->courier_service)
                    <div>
                        <span class="text-sm font-medium text-blue-700">Courier Service:</span>
                        <p class="text-sm text-blue-900 font-medium">{{ strtoupper($order->courier_service) }}</p>
                    </div>
                    @endif
                    @if($order->tracking_number)
                    <div>
                        <span class="text-sm font-medium text-blue-700">Tracking Number:</span>
                        <p class="text-sm text-blue-900 font-mono">{{ $order->tracking_number }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($order->waybill_data)
        @php
            $waybillData = json_decode($order->waybill_data, true);
        @endphp
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="font-medium text-gray-900 mb-3">Waybill Data (Raw)</h4>
            <div class="bg-gray-50 rounded-lg p-4">
                <pre class="text-xs text-gray-700 overflow-x-auto">{{ json_encode($waybillData, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Pickup Information -->
    @if(isset($order->metadata['pickup_request']))
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6 no-print">
        <div class="flex items-center mb-4">
            <i class="fas fa-truck text-orange-500 text-xl mr-3"></i>
            <h3 class="text-lg font-semibold text-gray-900">Informasi Pickup</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-orange-50 rounded-lg p-4">
                <h4 class="font-medium text-orange-900 mb-3">Pickup Details</h4>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-orange-700">Tanggal Pickup:</span>
                        <p class="text-sm text-orange-900">{{ $order->metadata['pickup_request']['pickup_date'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-orange-700">Waktu Pickup:</span>
                        <p class="text-sm text-orange-900">{{ $order->metadata['pickup_request']['pickup_time'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-orange-700">Status:</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            @if($order->status === 'picked_up') bg-green-100 text-green-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            @if($order->status === 'picked_up')
                                Sudah Diambil
                            @else
                                Menunggu Pickup
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            <div class="bg-blue-50 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-3">Pickup Address</h4>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-blue-700">Alamat:</span>
                        <p class="text-sm text-blue-900">{{ $order->metadata['pickup_request']['pickup_address'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-blue-700">Kontak:</span>
                        <p class="text-sm text-blue-900">{{ $order->metadata['pickup_request']['pickup_contact'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-blue-700">Telepon:</span>
                        <p class="text-sm text-blue-900">{{ $order->metadata['pickup_request']['pickup_phone'] ?? 'N/A' }}</p>
                    </div>
                    @if(isset($order->metadata['pickup_request']['notes']) && !empty($order->metadata['pickup_request']['notes']))
                    <div>
                        <span class="text-sm font-medium text-blue-700">Catatan:</span>
                        <p class="text-sm text-blue-900">{{ $order->metadata['pickup_request']['notes'] }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Order Status Information -->
    <div class="mt-6 pt-6 border-t border-gray-200">
        <h4 class="font-medium text-gray-900 mb-3">Order Status</h4>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm font-medium text-gray-600">Current Status:</span>
                    <p class="text-sm text-gray-900 mt-1 font-medium">
                        @switch($order->status)
                            @case('pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                                @break
                            @case('confirmed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Confirmed
                                </span>
                                @break
                            @case('assigned')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Assigned to Courier
                                </span>
                                @break
                            @case('picked_up')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Picked Up
                                </span>
                                @break
                            @case('in_transit')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    In Transit
                                </span>
                                @break
                            @case('delivered')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Delivered
                                </span>
                                @break
                            @case('cancelled')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Cancelled
                                </span>
                                @break
                            @default
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ ucfirst($order->status) }}
                                </span>
                        @endswitch
                    </p>
                </div>
                @if($order->tracking_number)
                <div>
                    <span class="text-sm font-medium text-gray-600">Tracking Number:</span>
                    <p class="text-sm text-gray-900 mt-1 font-mono">{{ $order->tracking_number }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Print Waybill Section -->
    @if($order->tracking_number && $order->shipping_method === 'rajaongkir')
    <div id="waybill-print-section" class="hidden">
        <div class="max-w-4xl mx-auto p-8 bg-white">
            <!-- Waybill Header -->
            <div class="text-center border-b-2 border-gray-800 pb-4 mb-6">
                <h1 class="text-3xl font-bold text-gray-900">WAYBILL</h1>
                <p class="text-lg text-gray-600">PT. Afiyah Express</p>
                <p class="text-sm text-gray-500">Jl. Contoh No. 123, Makassar, Sulawesi Selatan</p>
            </div>

            <!-- Waybill Details -->
            <div class="grid grid-cols-2 gap-8 mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-3">SHIPPER</h3>
                    <div class="space-y-2">
                        <p class="font-medium">{{ $order->customer->name }}</p>
                        <p class="text-sm">{{ $order->origin_address }}</p>
                        <p class="text-sm">
                            @if($order->originCity)
                                {{ $order->originCity->name }}, {{ $order->originCity->province->name }}
                            @else
                                N/A
                            @endif
                        </p>
                        <p class="text-sm">Phone: {{ $order->customer->phone }}</p>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-3">RECEIVER</h3>
                    <div class="space-y-2">
                        <p class="font-medium">{{ $order->customer->name }}</p>
                        <p class="text-sm">{{ $order->destination_address }}</p>
                        <p class="text-sm">
                            @if($order->destinationCity)
                                {{ $order->destinationCity->name }}, {{ $order->destinationCity->province->name }}
                            @else
                                N/A
                            @endif
                        </p>
                        <p class="text-sm">Phone: {{ $order->customer->phone }}</p>
                    </div>
                </div>
            </div>

            <!-- Shipment Details -->
            <div class="border-t-2 border-gray-800 pt-4 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3">SHIPMENT DETAILS</h3>
                <div class="grid grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-medium">Tracking Number:</span>
                            <span class="font-mono">{{ $order->tracking_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Courier:</span>
                            <span>{{ strtoupper($order->courier_service) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Service:</span>
                            <span>{{ ucfirst($order->shipping_method) }}</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-medium">Weight:</span>
                            <span>{{ $order->item_weight }} kg</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Item Value:</span>
                            <span>Rp {{ number_format($order->item_price, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Total Cost:</span>
                            <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item Description -->
            <div class="border-t-2 border-gray-800 pt-4 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3">ITEM DESCRIPTION</h3>
                <p class="text-lg">{{ $order->item_description }}</p>
            </div>

            <!-- Footer -->
            <div class="border-t-2 border-gray-800 pt-4">
                <div class="grid grid-cols-3 gap-8 text-center">
                    <div>
                        <div class="border-2 border-gray-300 h-20 mb-2"></div>
                        <p class="text-sm">Shipper Signature</p>
                    </div>
                    <div>
                        <div class="border-2 border-gray-300 h-20 mb-2"></div>
                        <p class="text-sm">Courier Signature</p>
                    </div>
                    <div>
                        <div class="border-2 border-gray-300 h-20 mb-2"></div>
                        <p class="text-sm">Receiver Signature</p>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <p class="text-sm text-gray-500">Generated on {{ now()->format('d M Y H:i') }} WITA</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Pickup Info Modal -->
    <div id="pickup-info-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Informasi Pickup</h3>
                    <button onclick="closePickupInfoModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h4 class="font-medium text-blue-900 mb-3">Pickup Details</h4>
                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="font-medium text-blue-700">Tanggal:</span>
                                <p class="text-blue-900" id="pickup-date-info"></p>
                            </div>
                            <div>
                                <span class="font-medium text-blue-700">Waktu:</span>
                                <p class="text-blue-900" id="pickup-time-info"></p>
                            </div>
                            <div>
                                <span class="font-medium text-blue-700">Status:</span>
                                <p class="text-blue-900" id="pickup-status-info"></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 rounded-lg p-4">
                        <h4 class="font-medium text-green-900 mb-3">Pickup Address</h4>
                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="font-medium text-green-700">Alamat:</span>
                                <p class="text-green-900" id="pickup-address-info"></p>
                            </div>
                            <div>
                                <span class="font-medium text-green-700">Kontak:</span>
                                <p class="text-green-900" id="pickup-contact-info"></p>
                            </div>
                            <div>
                                <span class="font-medium text-green-700">Telepon:</span>
                                <p class="text-green-900" id="pickup-phone-info"></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button onclick="closePickupInfoModal()"
                                class="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pickup Request Modal -->
    <div id="pickup-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Request Pickup</h3>
                    <button onclick="closePickupModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="pickup-form" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Pickup</label>
                        <input type="date" name="pickup_date" required min="{{ date('Y-m-d') }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <p class="text-xs text-gray-500 mt-1">Pilih tanggal hari ini atau setelahnya</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Waktu Pickup</label>
                        <select name="pickup_time" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">Pilih Waktu</option>
                            <option value="08:00-10:00">08:00 - 10:00</option>
                            <option value="10:00-12:00">10:00 - 12:00</option>
                            <option value="13:00-15:00">13:00 - 15:00</option>
                            <option value="15:00-17:00">15:00 - 17:00</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Alamat Pickup</label>
                        <textarea name="pickup_address" required rows="3" minlength="10"
                                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                                  placeholder="Masukkan alamat lengkap pickup...">{{ $order->origin_address }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Minimal 10 karakter</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kontak Pickup</label>
                        <input type="text" name="pickup_contact" required minlength="2" value="{{ $order->customer->name ?? '' }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                               placeholder="Nama kontak pickup">
                        <p class="text-xs text-gray-500 mt-1">Minimal 2 karakter</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Telepon Pickup</label>
                        <input type="text" name="pickup_phone" required minlength="10" value="{{ $order->customer->phone ?? '' }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                               placeholder="Nomor telepon pickup">
                        <p class="text-xs text-gray-500 mt-1">Minimal 10 digit</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Catatan (Opsional)</label>
                        <textarea name="notes" rows="2"
                                  class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                                  placeholder="Catatan tambahan untuk kurir..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closePickupModal()"
                                class="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                            Request Pickup
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #waybill-print-section, #waybill-print-section * {
        visibility: visible;
    }
    #waybill-print-section {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .no-print {
        display: none !important;
    }
}
</style>
@endpush

@push('scripts')
<script>








function printWaybill() {
    const printSection = document.getElementById('waybill-print-section');
    if (printSection) {
        printSection.classList.remove('hidden');
        window.print();
        // Hide print section after printing
        setTimeout(() => {
            printSection.classList.add('hidden');
        }, 1000);
    }
}

function showPickupInfo() {
    const modal = document.getElementById('pickup-info-modal');

    // Get pickup data from order metadata
    const pickupData = @json($order->metadata['pickup_request'] ?? null);

    if (pickupData) {
        document.getElementById('pickup-date-info').textContent = pickupData.pickup_date || 'N/A';
        document.getElementById('pickup-time-info').textContent = pickupData.pickup_time || 'N/A';
        document.getElementById('pickup-address-info').textContent = pickupData.pickup_address || 'N/A';
        document.getElementById('pickup-contact-info').textContent = pickupData.pickup_contact || 'N/A';
        document.getElementById('pickup-phone-info').textContent = pickupData.pickup_phone || 'N/A';

        const status = @json($order->status) === 'picked_up' ? 'Sudah Diambil' : 'Menunggu Pickup';
        document.getElementById('pickup-status-info').textContent = status;
    }

    modal.classList.remove('hidden');
}

function closePickupInfoModal() {
    const modal = document.getElementById('pickup-info-modal');
    modal.classList.add('hidden');
}

function requestPickup() {
    const modal = document.getElementById('pickup-modal');
    modal.classList.remove('hidden');
}

function closePickupModal() {
    const modal = document.getElementById('pickup-modal');
    modal.classList.add('hidden');
}

// Handle pickup form submission
document.getElementById('pickup-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    // Frontend validation
    const errors = [];

    if (!data.pickup_date) {
        errors.push('Tanggal pickup harus diisi');
    } else {
        const pickupDate = new Date(data.pickup_date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (pickupDate < today) {
            errors.push('Tanggal pickup tidak boleh sebelum hari ini');
        }
    }

    if (!data.pickup_time) {
        errors.push('Waktu pickup harus dipilih');
    }

    if (!data.pickup_address || data.pickup_address.length < 10) {
        errors.push('Alamat pickup minimal 10 karakter');
    }

    if (!data.pickup_contact || data.pickup_contact.length < 2) {
        errors.push('Kontak pickup minimal 2 karakter');
    }

    if (!data.pickup_phone || data.pickup_phone.length < 10) {
        errors.push('Telepon pickup minimal 10 digit');
    }

    if (errors.length > 0) {
        alert('Validasi gagal:\n' + errors.join('\n'));
        return;
    }

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Memproses...';
    submitBtn.disabled = true;

    fetch(`{{ route('waybill.pickup', $order) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            let message = 'Pickup berhasil diminta!';
            if (result.data && result.data.message) {
                message += '\n\n' + result.data.message;
            }

            // Show WhatsApp notification link if available
            if (result.whatsapp_link) {
                message += '\n\nKirim notifikasi WhatsApp ke customer?';
                if (confirm(message)) {
                    window.open(result.whatsapp_link, '_blank');
                }
            } else {
                alert(message);
            }

            closePickupModal();
            // Reload page to show updated status
            window.location.reload();
        } else {
            let errorMessage = 'Gagal meminta pickup: ' + result.message;

            // Show detailed validation errors if available
            if (result.errors) {
                errorMessage += '\n\nDetail error:';
                Object.keys(result.errors).forEach(field => {
                    errorMessage += '\n- ' + field + ': ' + result.errors[field].join(', ');
                });
            }

            alert(errorMessage);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat meminta pickup');
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});
</script>
@endpush
@endif
@endsection
