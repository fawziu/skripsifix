@extends('layouts.app')

@section('title', 'Detail Keluhan - Afiyah')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Detail Keluhan</h1>
                <p class="mt-2 text-gray-600">Informasi lengkap keluhan pengiriman</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('complaints.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
                @if(Auth::user()->isAdmin() || Auth::user()->id === $complaint->user_id)
                <a href="{{ route('complaints.edit', $complaint) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Edit
                </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Complaint Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Complaint Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ $complaint->title }}</h2>
                        <p class="text-sm text-gray-500 mt-1">
                            Dibuat pada {{ $complaint->created_at->format('d M Y H:i') }}
                        </p>
                    </div>
                    <div class="flex items-center">
                        @switch($complaint->status)
                            @case('pending')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i>
                                    Menunggu
                                </span>
                                @break
                            @case('in_progress')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-spinner mr-1"></i>
                                    Sedang Diproses
                                </span>
                                @break
                            @case('resolved')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>
                                    Selesai
                                </span>
                                @break
                            @case('closed')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-times mr-1"></i>
                                    Ditutup
                                </span>
                                @break
                            @default
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ ucfirst($complaint->status) }}
                                </span>
                        @endswitch
                    </div>
                </div>

                <!-- Complaint Description -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Deskripsi Keluhan</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-700 leading-relaxed">{{ $complaint->description }}</p>
                    </div>
                </div>

                <!-- Related Order -->
                @if($complaint->order)
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Pesanan Terkait</h3>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-blue-900">Order #{{ $complaint->order->id }}</p>
                                <p class="text-sm text-blue-700">{{ $complaint->order->item_description }}</p>
                                <p class="text-sm text-blue-600">
                                    Status: 
                                    <span class="font-medium">{{ ucfirst($complaint->order->status) }}</span>
                                </p>
                            </div>
                            <a href="{{ route('orders.show', $complaint->order) }}" 
                               class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                                <i class="fas fa-eye mr-1"></i>
                                Lihat
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Complaint Category -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Kategori Keluhan</h3>
                    <div class="flex items-center">
                        @switch($complaint->category)
                            @case('delivery_delay')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    <i class="fas fa-clock mr-1"></i>
                                    Keterlambatan Pengiriman
                                </span>
                                @break
                            @case('damaged_item')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Barang Rusak
                                </span>
                                @break
                            @case('wrong_item')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    <i class="fas fa-exchange-alt mr-1"></i>
                                    Barang Salah
                                </span>
                                @break
                            @case('service_quality')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-star mr-1"></i>
                                    Kualitas Layanan
                                </span>
                                @break
                            @case('other')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-question-circle mr-1"></i>
                                    Lainnya
                                </span>
                                @break
                            @default
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ ucfirst($complaint->category) }}
                                </span>
                        @endswitch
                    </div>
                </div>

                <!-- Priority Level -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tingkat Prioritas</h3>
                    <div class="flex items-center">
                        @switch($complaint->priority)
                            @case('low')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-arrow-down mr-1"></i>
                                    Rendah
                                </span>
                                @break
                            @case('medium')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-minus mr-1"></i>
                                    Sedang
                                </span>
                                @break
                            @case('high')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    <i class="fas fa-arrow-up mr-1"></i>
                                    Tinggi
                                </span>
                                @break
                            @case('urgent')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Urgent
                                </span>
                                @break
                            @default
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ ucfirst($complaint->priority) }}
                                </span>
                        @endswitch
                    </div>
                </div>

                <!-- Admin Response -->
                @if($complaint->admin_response)
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Respon Admin</h3>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-user-tie text-green-600 text-lg"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-900">Admin Response</p>
                                <p class="text-sm text-green-700 mt-1">{{ $complaint->admin_response }}</p>
                                @if($complaint->admin_response_at)
                                <p class="text-xs text-green-600 mt-2">
                                    Direspon pada {{ $complaint->admin_response_at->format('d M Y H:i') }}
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Resolution Notes -->
                @if($complaint->resolution_notes)
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Catatan Penyelesaian</h3>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-blue-700">{{ $complaint->resolution_notes }}</p>
                        @if($complaint->resolved_at)
                        <p class="text-xs text-blue-600 mt-2">
                            Diselesaikan pada {{ $complaint->resolved_at->format('d M Y H:i') }}
                        </p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- User Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Pelapor</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Nama</p>
                        <p class="text-sm text-gray-900">{{ $complaint->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Email</p>
                        <p class="text-sm text-gray-900">{{ $complaint->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Telepon</p>
                        <p class="text-sm text-gray-900">{{ $complaint->user->phone }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Role</p>
                        <p class="text-sm text-gray-900">{{ $complaint->user->role->display_name }}</p>
                    </div>
                </div>
            </div>

            <!-- Complaint Timeline -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Timeline</h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-3 h-3 bg-blue-600 rounded-full"></div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Keluhan Dibuat</p>
                            <p class="text-xs text-gray-500">{{ $complaint->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>

                    @if($complaint->admin_response_at)
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-3 h-3 bg-green-600 rounded-full"></div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Admin Merespon</p>
                            <p class="text-xs text-gray-500">{{ $complaint->admin_response_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    @endif

                    @if($complaint->resolved_at)
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-3 h-3 bg-purple-600 rounded-full"></div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Diselesaikan</p>
                            <p class="text-xs text-gray-500">{{ $complaint->resolved_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    @endif

                    @if($complaint->updated_at && $complaint->updated_at != $complaint->created_at)
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-3 h-3 bg-gray-600 rounded-full"></div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Terakhir Diupdate</p>
                            <p class="text-xs text-gray-500">{{ $complaint->updated_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            @if(Auth::user()->isAdmin())
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Aksi Cepat</h3>
                <div class="space-y-3">
                    @if($complaint->status === 'pending')
                    <form method="POST" action="{{ route('complaints.update', $complaint) }}" class="inline w-full">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="in_progress">
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-play mr-2"></i>
                            Mulai Proses
                        </button>
                    </form>
                    @endif

                    @if($complaint->status === 'in_progress')
                    <form method="POST" action="{{ route('complaints.update', $complaint) }}" class="inline w-full">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="resolved">
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-check mr-2"></i>
                            Tandai Selesai
                        </button>
                    </form>
                    @endif

                    @if($complaint->status !== 'closed')
                    <form method="POST" action="{{ route('complaints.update', $complaint) }}" class="inline w-full">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="closed">
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-times mr-2"></i>
                            Tutup Keluhan
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
