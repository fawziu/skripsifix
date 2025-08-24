@extends('layouts.app')

@section('title', 'Order Detail - Afiyah')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Detail Pesanan #{{ $order->id }}</h1>
                <p class="mt-2 text-gray-600">Informasi lengkap pesanan pengiriman</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('orders.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
                @if(Auth::user()->isAdmin() && $order->status === 'pending')
                <a href="{{ route('orders.edit', $order) }}"
                   class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Edit
                </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Status -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Status Pesanan</h3>
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

                <!-- Status Timeline -->
                <div class="space-y-4">
                    @php
                        $statuses = [
                            'pending' => ['icon' => 'clock', 'title' => 'Pending', 'description' => 'Pesanan menunggu konfirmasi'],
                            'confirmed' => ['icon' => 'check-circle', 'title' => 'Confirmed', 'description' => 'Pesanan telah dikonfirmasi'],
                            'assigned' => ['icon' => 'user', 'title' => 'Assigned', 'description' => 'Kurir telah ditugaskan'],
                            'picked_up' => ['icon' => 'truck', 'title' => 'Picked Up', 'description' => 'Barang telah diambil'],
                            'in_transit' => ['icon' => 'shipping-fast', 'title' => 'In Transit', 'description' => 'Barang dalam perjalanan'],
                            'delivered' => ['icon' => 'check-double', 'title' => 'Delivered', 'description' => 'Barang telah diterima']
                        ];
                    @endphp

                    @foreach($statuses as $status => $info)
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center
                                    @if(array_search($status, array_keys($statuses)) <= array_search($order->status, array_keys($statuses)))
                                        bg-blue-100 text-blue-600
                                    @else
                                        bg-gray-100 text-gray-400
                                    @endif">
                                    <i class="fas fa-{{ $info['icon'] }} text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $info['title'] }}</p>
                                <p class="text-sm text-gray-500">{{ $info['description'] }}</p>
                            </div>
                            @if(array_search($status, array_keys($statuses)) <= array_search($order->status, array_keys($statuses)))
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check text-green-500"></i>
                                </div>
                            @endif
                        </div>
                        @if(!$loop->last)
                            <div class="ml-4 border-l-2 border-gray-200 h-4"></div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Item Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Barang</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm font-medium text-gray-600">Deskripsi:</span>
                        <p class="text-sm text-gray-900 mt-1">{{ $order->item_description }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Berat:</span>
                        <p class="text-sm text-gray-900 mt-1">{{ $order->item_weight ?? 0 }} kg</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Nilai Barang:</span>
                        <p class="text-sm text-gray-900 mt-1">Rp {{ number_format($order->item_price ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-600">Metode Pengiriman:</span>
                        <p class="text-sm text-gray-900 mt-1">{{ ucfirst($order->shipping_method) }}</p>
                    </div>
                </div>
            </div>

            <!-- Shipping Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Pengiriman</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-2">Alamat Asal</h4>
                        <p class="text-sm text-gray-900">{{ $order->origin_address }}</p>
                        @if($order->origin_city)
                            <p class="text-sm text-gray-500 mt-1">{{ $order->origin_city }}</p>
                        @endif
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-2">Alamat Tujuan</h4>
                        <p class="text-sm text-gray-900">{{ $order->destination_address }}</p>
                        @if($order->destination_city)
                            <p class="text-sm text-gray-500 mt-1">{{ $order->destination_city }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Status History -->
            @if($order->statusHistory && $order->statusHistory->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Status</h3>
                <div class="space-y-4">
                    @foreach($order->statusHistory as $history)
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-history text-blue-600 text-sm"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">
                                {{ ucfirst(str_replace('_', ' ', $history->status)) }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $history->created_at->format('d M Y H:i') }}
                            </p>
                            @if($history->notes)
                                <p class="text-sm text-gray-600 mt-1">{{ $history->notes }}</p>
                            @endif
                            @if($history->updatedBy)
                                <p class="text-xs text-gray-400 mt-1">
                                    Diperbarui oleh: {{ $history->updatedBy->name }}
                                </p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Tracking Information for Customer -->
            @if(Auth::user()->isCustomer() && $order->shipping_method === 'rajaongkir' && $order->tracking_number)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Tracking</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Tracking Number</p>
                            <p class="text-sm text-gray-600 font-mono">{{ $order->tracking_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Courier</p>
                            <p class="text-sm text-gray-600">{{ strtoupper($order->courier_service) }}</p>
                        </div>
                    </div>
                    <div id="tracking-info" class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-center">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                            <span class="ml-3 text-gray-600">Memuat informasi tracking...</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Order Summary -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Pesanan</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Nilai Barang:</span>
                        <span class="text-sm font-medium">Rp {{ number_format($order->item_price ?? 0, 0, ',', '.') }}</span>
                    </div>
                    @if($order->service_fee)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Biaya Layanan:</span>
                        <span class="text-sm font-medium">Rp {{ number_format($order->service_fee ?? 0, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($order->shipping_cost)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Biaya Pengiriman:</span>
                        <span class="text-sm font-medium">Rp {{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="border-t pt-3">
                        <div class="flex justify-between">
                            <span class="text-base font-semibold text-gray-900">Total:</span>
                            <span class="text-base font-semibold text-gray-900">Rp {{ number_format($order->total_amount ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pengguna</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-600">Customer:</span>
                        <p class="text-sm text-gray-900">{{ $order->customer->name }}</p>
                        <p class="text-sm text-gray-500">{{ $order->customer->email }}</p>
                        <p class="text-sm text-gray-500">{{ $order->customer->phone }}</p>
                    </div>
                    @if($order->courier)
                    <div class="border-t pt-3">
                        <span class="text-sm font-medium text-gray-600">Courier:</span>
                        <p class="text-sm text-gray-900">{{ $order->courier->name }}</p>
                        <p class="text-sm text-gray-500">{{ $order->courier->phone }}</p>
                    </div>
                    @endif
                    @if($order->admin)
                    <div class="border-t pt-3">
                        <span class="text-sm font-medium text-gray-600">Admin:</span>
                        <p class="text-sm text-gray-900">{{ $order->admin->name }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi</h3>
                <div class="space-y-3">
                    @if(Auth::user()->isAdmin())
                    <a href="{{ route('orders.track', $order) }}"
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-truck mr-2"></i>
                        Lacak Pesanan
                    </a>
                    @endif

                    @if(Auth::user()->isCourier() && Auth::user()->id === $order->courier_id)
                    <a href="{{ route('courier.orders.show', $order) }}"
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-truck mr-2"></i>
                        Update Status
                    </a>
                    @endif

                    @if($order->tracking_number && $order->shipping_method === 'rajaongkir')
                    <button type="button" onclick="generateLabel()"
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 transition-colors">
                        <i class="fas fa-print mr-2"></i>
                        Generate Label
                    </button>
                    @endif

                    @if(Auth::user()->isAdmin() && $order->status === 'pending')
                    <button type="button" onclick="confirmOrder()"
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-check mr-2"></i>
                        Konfirmasi Pesanan
                    </button>
                    @endif

                    @if(Auth::user()->isAdmin() && $order->status === 'confirmed' && !$order->courier_id)
                    <button type="button" onclick="assignCourier()"
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>
                        Tugaskan Kurir
                    </button>
                    @endif

                    <a href="{{ route('complaints.create', ['order_id' => $order->id]) }}"
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Laporkan Masalah
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmOrder() {
    if (confirm('Apakah Anda yakin ingin mengkonfirmasi pesanan ini?')) {
        // Add confirmation logic here
        console.log('Confirming order...');
    }
}

function assignCourier() {
    if (confirm('Apakah Anda yakin ingin menugaskan kurir untuk pesanan ini?')) {
        // Add courier assignment logic here
        console.log('Assigning courier...');
    }
}

function generateLabel() {
    if (confirm('Apakah Anda yakin ingin generate label pengiriman?')) {
        fetch(`/api/orders/{{ $order->id }}/generate-label`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Label berhasil di-generate!');
                if (data.label_url) {
                    window.open(data.label_url, '_blank');
                }
            } else {
                alert('Gagal generate label: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat generate label');
        });
    }
}

// Load tracking info for customer
document.addEventListener('DOMContentLoaded', function() {
    const trackingInfo = document.getElementById('tracking-info');
    if (trackingInfo) {
        loadTrackingInfo();
    }
});

function loadTrackingInfo() {
    const trackingInfo = document.getElementById('tracking-info');

    fetch(`/api/orders/{{ $order->id }}/track`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data) {
            displayTrackingInfo(data.data);
        } else {
            showTrackingError(data.message || 'Gagal memuat informasi tracking');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showTrackingError('Terjadi kesalahan saat memuat informasi tracking');
    });
}

function displayTrackingInfo(trackingData) {
    const trackingInfo = document.getElementById('tracking-info');

    if (trackingData.manifest && trackingData.manifest.length > 0) {
        let html = '<div class="space-y-3">';
        trackingData.manifest.slice(0, 5).forEach((item, index) => {
            html += `
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-truck text-blue-600 text-xs"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">${item.manifest_description || 'Update Status'}</p>
                        <p class="text-xs text-gray-500">${item.manifest_date || 'N/A'} ${item.manifest_time || ''}</p>
                        ${item.city_name ? `<p class="text-xs text-gray-600 mt-1">${item.city_name}</p>` : ''}
                    </div>
                </div>
                ${index < Math.min(4, trackingData.manifest.length - 1) ? '<div class="ml-3 border-l-2 border-gray-200 h-3"></div>' : ''}
            `;
        });
        html += '</div>';
        trackingInfo.innerHTML = html;
    } else {
        trackingInfo.innerHTML = `
            <div class="text-center text-gray-500">
                <i class="fas fa-info-circle text-2xl mb-2"></i>
                <p class="text-sm">Belum ada informasi tracking yang tersedia</p>
            </div>
        `;
    }
}

function showTrackingError(message) {
    const trackingInfo = document.getElementById('tracking-info');
    trackingInfo.innerHTML = `
        <div class="text-center text-red-500">
            <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
            <p class="text-sm">${message}</p>
        </div>
    `;
}
</script>
@endpush
@endsection
