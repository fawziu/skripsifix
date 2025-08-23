@extends('layouts.app')

@section('title', 'Create Complaint - Afiyah')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Laporkan Masalah</h1>
                <p class="mt-2 text-gray-600">Buat laporan keluhan atau masalah baru</p>
            </div>
            <a href="{{ route('complaints.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Complaint Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form method="POST" action="{{ route('complaints.store') }}" class="p-6">
            @csrf
            
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
                                   placeholder="Masukkan judul keluhan Anda"
                                   value="{{ old('title') }}">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Type -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                                Tipe Keluhan <span class="text-red-500">*</span>
                            </label>
                            <select id="type" name="type" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                                <option value="">Pilih tipe keluhan</option>
                                <option value="delivery" {{ old('type') == 'delivery' ? 'selected' : '' }}>Delivery</option>
                                <option value="service" {{ old('type') == 'service' ? 'selected' : '' }}>Service</option>
                                <option value="payment" {{ old('type') == 'payment' ? 'selected' : '' }}>Payment</option>
                                <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
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
                                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                            @error('priority')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Order ID (if related to order) -->
                        @if(request('order_id'))
                        <div class="md:col-span-2">
                            <label for="order_id" class="block text-sm font-medium text-gray-700 mb-1">
                                ID Pesanan Terkait
                            </label>
                            <input type="hidden" name="order_id" value="{{ request('order_id') }}">
                            <div class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-700">
                                #{{ request('order_id') }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Keluhan</h3>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Deskripsi Masalah <span class="text-red-500">*</span>
                        </label>
                        <textarea id="description" name="description" rows="6" required
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                  placeholder="Jelaskan detail masalah yang Anda alami...">{{ old('description') }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">
                            Berikan detail lengkap tentang masalah yang Anda alami agar kami dapat membantu dengan lebih baik.
                        </p>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Additional Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Tambahan</h3>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-blue-800">Tips untuk laporan yang lebih baik:</h4>
                                <div class="mt-2 text-sm text-blue-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Sertakan tanggal dan waktu kejadian</li>
                                        <li>Berikan detail spesifik tentang masalah</li>
                                        <li>Jika terkait pesanan, sertakan ID pesanan</li>
                                        <li>Jelaskan dampak masalah terhadap Anda</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-6">
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-3 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Kirim Laporan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
