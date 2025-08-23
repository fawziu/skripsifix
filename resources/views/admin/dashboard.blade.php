@extends('layouts.app')

@section('title', 'Admin Dashboard - Afiyah')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="mt-2 text-gray-600">Selamat datang, {{ Auth::user()->name }}! Kelola sistem Afiyah Anda.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Orders -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-box text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Pesanan</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalOrders ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pesanan Pending</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $pendingOrders ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Active Couriers -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-truck text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Kurir Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $activeCouriers ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Open Complaints -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Komplain Terbuka</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $openComplaints ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Pesanan Terbaru</h3>
            </div>
            <div class="p-6">
                @if(isset($recentOrders) && $recentOrders->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentOrders as $order)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">#{{ $order->id }}</p>
                                <p class="text-sm text-gray-500">{{ Str::limit($order->item_description, 30) }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                                    @elseif($order->status === 'delivered') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                                <p class="text-sm text-gray-500 mt-1">{{ $order->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('orders.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Lihat Semua Pesanan →
                        </a>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-box text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">Belum ada pesanan</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Complaints -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Komplain Terbaru</h3>
            </div>
            <div class="p-6">
                @if(isset($recentComplaints) && $recentComplaints->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentComplaints as $complaint)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">{{ $complaint->title }}</p>
                                <p class="text-sm text-gray-500">{{ $complaint->user->name }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    @if($complaint->priority === 'urgent') bg-red-100 text-red-800
                                    @elseif($complaint->priority === 'high') bg-orange-100 text-orange-800
                                    @elseif($complaint->priority === 'medium') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800 @endif">
                                    {{ ucfirst($complaint->priority) }}
                                </span>
                                <p class="text-sm text-gray-500 mt-1">{{ $complaint->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('complaints.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Lihat Semua Komplain →
                        </a>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-triangle text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">Belum ada komplain</p>
                    </div>
                @endif
            </div>
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
                    <p class="font-medium text-blue-900">Kelola Pesanan</p>
                    <p class="text-sm text-blue-700">Lihat dan kelola semua pesanan</p>
                </div>
            </a>
            
            <a href="{{ route('complaints.index') }}" 
               class="flex items-center p-4 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl mr-3"></i>
                <div>
                    <p class="font-medium text-red-900">Kelola Komplain</p>
                    <p class="text-sm text-red-700">Tangani komplain pelanggan</p>
                </div>
            </a>
            
            <a href="{{ route('users.index') }}" 
               class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <i class="fas fa-users text-green-600 text-xl mr-3"></i>
                <div>
                    <p class="font-medium text-green-900">Kelola Pengguna</p>
                    <p class="text-sm text-green-700">Kelola customer dan kurir</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
