@extends('layouts.app')

@section('title', 'Edit Keluhan - Afiyah')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Keluhan</h1>
                <p class="mt-2 text-gray-600">Ubah detail keluhan pengiriman</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('complaints.show', $complaint) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form method="POST" action="{{ route('complaints.update', $complaint) }}" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Dasar</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Title -->
                        <div class="md:col-span-2">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                                Judul Keluhan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="title" name="title" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                   placeholder="Masukkan judul keluhan"
                                   value="{{ old('title', $complaint->title) }}">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Type -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                                Tipe <span class="text-red-500">*</span>
                            </label>
                            <select id="type" name="type" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                                <option value="">Pilih Tipe</option>
                                <option value="delivery" {{ old('type', $complaint->type) === 'delivery' ? 'selected' : '' }}>
                                    Pengiriman
                                </option>
                                <option value="service" {{ old('type', $complaint->type) === 'service' ? 'selected' : '' }}>
                                    Layanan
                                </option>
                                <option value="payment" {{ old('type', $complaint->type) === 'payment' ? 'selected' : '' }}>
                                    Pembayaran
                                </option>
                                <option value="other" {{ old('type', $complaint->type) === 'other' ? 'selected' : '' }}>
                                    Lainnya
                                </option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Priority -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">
                                Prioritas
                            </label>
                            <select id="priority" name="priority"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('priority') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                                <option value="low" {{ old('priority', $complaint->priority) === 'low' ? 'selected' : '' }}>
                                    Rendah
                                </option>
                                <option value="medium" {{ old('priority', $complaint->priority) === 'medium' ? 'selected' : '' }}>
                                    Sedang
                                </option>
                                <option value="high" {{ old('priority', $complaint->priority) === 'high' ? 'selected' : '' }}>
                                    Tinggi
                                </option>
                                <option value="urgent" {{ old('priority', $complaint->priority) === 'urgent' ? 'selected' : '' }}>
                                    Urgent
                                </option>
                            </select>
                            @error('priority')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Deskripsi Keluhan</h3>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Deskripsi <span class="text-red-500">*</span>
                        </label>
                        <textarea id="description" name="description" rows="5" required
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                  placeholder="Jelaskan detail keluhan Anda">{{ old('description', $complaint->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Related Order -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Pesanan Terkait</h3>
                    
                    <div>
                        <label for="order_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Pesanan (Opsional)
                        </label>
                        <select id="order_id" name="order_id"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('order_id') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                            <option value="">Tidak ada pesanan terkait</option>
                            @if(Auth::user()->isAdmin())
                                <!-- Admin can see all orders -->
                                @foreach(\App\Models\Order::with('customer')->get() as $order)
                                    <option value="{{ $order->id }}" {{ old('order_id', $complaint->order_id) == $order->id ? 'selected' : '' }}>
                                        Order #{{ $order->id }} - {{ $order->customer->name ?? 'Unknown' }} - {{ $order->item_description }}
                                    </option>
                                @endforeach
                            @else
                                <!-- Regular users can only see their own orders -->
                                @foreach(Auth::user()->orders as $order)
                                    <option value="{{ $order->id }}" {{ old('order_id', $complaint->order_id) == $order->id ? 'selected' : '' }}>
                                        Order #{{ $order->id }} - {{ $order->item_description }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('order_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">
                            Pilih pesanan jika keluhan terkait dengan pesanan tertentu
                        </p>
                    </div>
                </div>

                <!-- Admin Only Fields -->
                @if(Auth::user()->isAdmin())
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Pengelolaan Admin</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                            </label>
                            <select id="status" name="status"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                                <option value="open" {{ old('status', $complaint->status) === 'open' ? 'selected' : '' }}>
                                    Terbuka
                                </option>
                                <option value="in_progress" {{ old('status', $complaint->status) === 'in_progress' ? 'selected' : '' }}>
                                    Sedang Diproses
                                </option>
                                <option value="resolved" {{ old('status', $complaint->status) === 'resolved' ? 'selected' : '' }}>
                                    Selesai
                                </option>
                                <option value="closed" {{ old('status', $complaint->status) === 'closed' ? 'selected' : '' }}>
                                    Ditutup
                                </option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Assigned To -->
                        <div>
                            <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">
                                Ditugaskan Kepada
                            </label>
                            <select id="assigned_to" name="assigned_to"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('assigned_to') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                                <option value="">Tidak ada</option>
                                @foreach(\App\Models\User::where('role_id', 1)->get() as $admin)
                                    <option value="{{ $admin->id }}" {{ old('assigned_to', $complaint->assigned_to) == $admin->id ? 'selected' : '' }}>
                                        {{ $admin->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Resolution -->
                    <div class="mt-6">
                        <label for="resolution" class="block text-sm font-medium text-gray-700 mb-1">
                            Catatan Penyelesaian
                        </label>
                        <textarea id="resolution" name="resolution" rows="4"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('resolution') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                  placeholder="Catatan tentang bagaimana keluhan diselesaikan">{{ old('resolution', $complaint->resolution) }}</textarea>
                        @error('resolution')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">
                            Catatan internal tentang penyelesaian keluhan
                        </p>
                    </div>
                </div>
                @endif

                <!-- Current Complaint Info -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Informasi Keluhan Saat Ini</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Status:</span>
                            <span class="ml-2 font-medium text-gray-900">
                                @switch($complaint->status)
                                    @case('open')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>Terbuka
                                        </span>
                                        @break
                                    @case('in_progress')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-spinner mr-1"></i>Sedang Diproses
                                        </span>
                                        @break
                                    @case('resolved')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>Selesai
                                        </span>
                                        @break
                                    @case('closed')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-times mr-1"></i>Ditutup
                                        </span>
                                        @break
                                    @default
                                        {{ ucfirst($complaint->status) }}
                                @endswitch
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500">Dibuat:</span>
                            <span class="ml-2 font-medium text-gray-900">{{ $complaint->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Terakhir Update:</span>
                            <span class="ml-2 font-medium text-gray-900">{{ $complaint->updated_at->format('d M Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Pelapor:</span>
                            <span class="ml-2 font-medium text-gray-900">{{ $complaint->user->name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3 pt-6">
                    <a href="{{ route('complaints.show', $complaint) }}" 
                       class="inline-flex items-center px-6 py-3 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection