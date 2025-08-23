@extends('layouts.app')

@section('title', 'Courier Dashboard - Afiyah')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Courier Dashboard</h1>
        <p class="mt-2 text-gray-600">Selamat datang, {{ Auth::user()->name }}! Kelola pengiriman Anda.</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $assignedOrders ?? 0 }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $inProgressOrders ?? 0 }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $completedToday ?? 0 }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $totalDelivered ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Deliveries -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Pengiriman Saat Ini</h3>
        </div>
        <div class="p-6">
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
                            <a href="{{ route('orders.show', $order) }}" 
                               class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                                <i class="fas fa-eye mr-1"></i>
                                Detail
                            </a>
                            <a href="{{ route('orders.track', $order) }}" 
                               class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition-colors">
                                <i class="fas fa-truck mr-1"></i>
                                Update Status
                            </a>
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
        <div class="p-6">
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
                            <p class="text-sm font-medium text-gray-900">Rp {{ number_format($order->total_cost, 0, ',', '.') }}</p>
                            <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:text-blue-800 text-sm">
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

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('orders.index') }}" 
               class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <i class="fas fa-box text-blue-600 text-xl mr-3"></i>
                <div>
                    <p class="font-medium text-blue-900">Lihat Pesanan</p>
                    <p class="text-sm text-blue-700">Lihat semua pesanan yang ditugaskan</p>
                </div>
            </a>
            
            <a href="#" 
               class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <i class="fas fa-route text-green-600 text-xl mr-3"></i>
                <div>
                    <p class="font-medium text-green-900">Rute Pengiriman</p>
                    <p class="text-sm text-green-700">Optimalkan rute pengiriman</p>
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
