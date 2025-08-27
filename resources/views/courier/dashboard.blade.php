@extends('layouts.app')

@section('title', 'Courier Dashboard - Afiyah')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Courier Dashboard</h1>
                <p class="mt-2 text-gray-600">Selamat datang, {{ Auth::user()->name }}! Kelola pengiriman Anda.</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('courier.orders.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-list mr-2"></i>
                    Kelola Pesanan
                </a>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-sm text-gray-600">Real-time aktif</span>
                </div>
                <div class="text-sm text-gray-500">WITA</div>
                <button onclick="updateDashboardData()"
                        class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-refresh mr-2"></i>
                    Refresh
                </button>
            </div>
        </div>
        <div class="mt-2 text-sm text-gray-500">
            Last update: <span id="last-update">{{ now()->format('d M Y H:i') }} WITA</span>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Assigned Orders -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-box text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pesanan Ditugaskan</p>
                    <p class="text-2xl font-bold text-gray-900" data-stat="assigned-orders">{{ $assignedOrders ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- In Progress -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-truck text-orange-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Dalam Pengiriman</p>
                    <p class="text-2xl font-bold text-gray-900" data-stat="in-progress-orders">{{ $inProgressOrders ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Completed Today -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Selesai Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-900" data-stat="completed-today">{{ $completedToday ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Total Delivered -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-star text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Terkirim</p>
                    <p class="text-2xl font-bold text-gray-900" data-stat="total-delivered">{{ $totalDelivered ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Deliveries -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Pengiriman Saat Ini</h3>
        </div>
        <div class="p-6" id="current-deliveries-container">
            @if(isset($currentDeliveries) && $currentDeliveries->count() > 0)
                <div class="space-y-4">
                    @foreach($currentDeliveries as $order)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-medium text-gray-900">#{{ $order->id }} - {{ $order->customer->name }}</h4>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    @if($order->status === 'assigned') bg-blue-100 text-blue-800
                                    @elseif($order->status === 'picked_up') bg-purple-100 text-purple-800
                                    @elseif($order->status === 'in_transit') bg-orange-100 text-orange-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">{{ Str::limit($order->item_description, 50) }}</p>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                <span>{{ Str::limit($order->destination_address, 60) }}</span>
                            </div>
                        </div>
                        <div class="ml-4 flex flex-col space-y-2">
                            <a href="{{ route('courier.orders.show', $order) }}"
                               class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                                <i class="fas fa-eye mr-1"></i>
                                Detail
                            </a>

                            @if($order->status === 'assigned' && !$order->pickup_proof_photo)
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                    <i class="fas fa-camera mr-1"></i>
                                    Perlu Bukti Pengambilan
                                </span>
                            @elseif($order->status === 'picked_up' && !$order->delivery_proof_photo)
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded-full">
                                    <i class="fas fa-camera mr-1"></i>
                                    Perlu Bukti Pengiriman
                                </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-truck text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500 text-lg font-medium">Tidak ada pengiriman saat ini</p>
                    <p class="text-gray-400 text-sm mt-1">Anda akan diberitahu ketika ada pesanan baru</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Completed Deliveries -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Pengiriman Terbaru Selesai</h3>
        </div>
        <div class="p-6" id="recent-completed-container">
            @if(isset($recentCompleted) && $recentCompleted->count() > 0)
                <div class="space-y-4">
                    @foreach($recentCompleted as $order)
                    <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">#{{ $order->id }} - {{ $order->customer->name }}</p>
                            <p class="text-sm text-gray-600">{{ Str::limit($order->item_description, 40) }}</p>
                            <p class="text-sm text-green-600 mt-1">
                                <i class="fas fa-check-circle mr-1"></i>
                                Selesai pada {{ $order->updated_at->format('d M Y H:i') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                            <a href="{{ route('courier.orders.show', $order) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-check-circle text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500">Belum ada pengiriman selesai</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Complaints -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Keluhan Terbaru</h3>
                <a href="{{ route('complaints.index') }}" class="text-red-600 hover:text-red-800 text-sm font-medium">
                    Lihat Semua →
                </a>
            </div>
        </div>
        <div class="p-6" id="recent-complaints-container">
            @if(isset($recentComplaints) && $recentComplaints->count() > 0)
                <div class="space-y-4">
                    @foreach($recentComplaints as $complaint)
                    <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">{{ $complaint->title }}</p>
                            <p class="text-sm text-gray-600">{{ Str::limit($complaint->description, 40) }}</p>
                            <p class="text-sm text-red-600 mt-1">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                {{ $complaint->customer->name }} - {{ $complaint->created_at->format('d M Y') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                @if($complaint->status === 'open') bg-red-100 text-red-800
                                @elseif($complaint->status === 'in_progress') bg-yellow-100 text-yellow-800
                                @elseif($complaint->status === 'resolved') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500">Belum ada keluhan</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('courier.orders.index') }}"
               class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <i class="fas fa-box text-blue-600 text-xl mr-3"></i>
                <div>
                    <p class="font-medium text-blue-900">Kelola Pesanan</p>
                    <p class="text-sm text-blue-700">Lihat dan update pesanan yang ditugaskan</p>
                </div>
            </a>



            <a href="{{ route('complaints.index') }}"
               class="flex items-center p-4 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl mr-3"></i>
                <div>
                    <p class="font-medium text-red-900">Keluhan Pengiriman</p>
                    <p class="text-sm text-red-700">Lihat keluhan terkait pengiriman</p>
                </div>
            </a>

            <a href="{{ route('profile') }}"
               class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <i class="fas fa-user text-purple-600 text-xl mr-3"></i>
                <div>
                    <p class="font-medium text-purple-900">Profil Saya</p>
                    <p class="text-sm text-purple-700">Update informasi profil</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    updateDashboardData();

    // Update every 30 seconds
    setInterval(updateDashboardData, 30000);
});

function updateDashboardData() {
    fetch('/courier/dashboard/data', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update statistics
            document.querySelector('[data-stat="assigned-orders"]').textContent = data.data.assignedOrders;
            document.querySelector('[data-stat="in-progress-orders"]').textContent = data.data.inProgressOrders;
            document.querySelector('[data-stat="completed-today"]').textContent = data.data.completedToday;
            document.querySelector('[data-stat="total-delivered"]').textContent = data.data.totalDelivered;

            // Update current deliveries
            updateCurrentDeliveries(data.data.currentDeliveries);

            // Update recent completed
            updateRecentCompleted(data.data.recentCompleted);

            // Update recent complaints
            updateRecentComplaints(data.data.recentComplaints);

            // Update timestamp
            document.getElementById('last-update').textContent = new Date().toLocaleString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }) + ' WITA';
        }
    })
    .catch(error => {
        console.error('Error updating dashboard:', error);
    });
}

function updateCurrentDeliveries(deliveries) {
    const container = document.getElementById('current-deliveries-container');

    if (deliveries.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-truck text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500 text-lg font-medium">Tidak ada pengiriman saat ini</p>
                <p class="text-gray-400 text-sm mt-1">Anda akan diberitahu ketika ada pesanan baru</p>
            </div>
        `;
        return;
    }

    let html = '<div class="space-y-4">';
    deliveries.forEach(delivery => {
        const statusClass = delivery.status === 'assigned' ? 'bg-blue-100 text-blue-800' :
                           delivery.status === 'picked_up' ? 'bg-purple-100 text-purple-800' :
                           'bg-orange-100 text-orange-800';

        html += `
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium text-gray-900">#${delivery.id} - ${delivery.customer_name}</h4>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                            ${delivery.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">${delivery.item_description}</p>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        <span>${delivery.destination_address}</span>
                    </div>
                </div>
                <div class="ml-4 flex flex-col space-y-2">
                    <a href="/courier/orders/${delivery.id}/detail"
                       class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                        <i class="fas fa-eye mr-1"></i>
                        Detail
                    </a>
                    <a href="/courier/orders/${delivery.id}/detail"
                       class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition-colors">
                        <i class="fas fa-truck mr-1"></i>
                        Update Status
                    </a>

                    ${delivery.status === 'assigned' && !delivery.pickup_proof_photo ?
                        `<span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                            <i class="fas fa-camera mr-1"></i>
                            Perlu Bukti Pengambilan
                        </span>` : ''
                    }
                    ${delivery.status === 'picked_up' && !delivery.delivery_proof_photo ?
                        `<span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded-full">
                            <i class="fas fa-camera mr-1"></i>
                            Perlu Bukti Pengiriman
                        </span>` : ''
                    }
                </div>
            </div>
        `;
    });
    html += '</div>';

    container.innerHTML = html;
}

function updateRecentCompleted(completed) {
    const container = document.getElementById('recent-completed-container');

    if (completed.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-check-circle text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500">Belum ada pengiriman selesai</p>
            </div>
        `;
        return;
    }

    let html = '<div class="space-y-4">';
    completed.forEach(order => {
        html += `
            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-900">#${order.id} - ${order.customer_name}</p>
                    <p class="text-sm text-gray-600">${order.item_description}</p>
                    <p class="text-sm text-green-600 mt-1">
                        <i class="fas fa-check-circle mr-1"></i>
                        Selesai pada ${order.completed_at}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-900">Rp ${order.total_cost.toLocaleString('id-ID')}</p>
                    <a href="/courier/orders/${order.id}/detail" class="text-blue-600 hover:text-blue-800 text-sm">
                        Lihat Detail
                    </a>
                </div>
            </div>
        `;
    });
    html += '</div>';

    container.innerHTML = html;
}

function updateRecentComplaints(complaints) {
    const container = document.getElementById('recent-complaints-container');

    if (complaints.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500">Belum ada keluhan</p>
            </div>
        `;
        return;
    }

    let html = '<div class="space-y-4">';
    complaints.forEach(complaint => {
        const statusClass = complaint.status === 'open' ? 'bg-red-100 text-red-800' :
                           complaint.status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' :
                           'bg-green-100 text-green-800';

        html += `
            <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-900">${complaint.title}</p>
                    <p class="text-sm text-gray-600">${complaint.type}</p>
                    <p class="text-sm text-red-600 mt-1">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        ${complaint.customer_name} - ${complaint.created_at}
                    </p>
                </div>
                <div class="text-right">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                        ${complaint.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                    </span>
                </div>
            </div>
        `;
    });
    html += '</div>';

    container.innerHTML = html;
}
</script>
@endpush
