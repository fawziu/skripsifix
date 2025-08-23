@extends('layouts.app')

@section('title', 'Alamat Saya - Afiyah')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Alamat Saya</h1>
                <p class="mt-2 text-gray-600">Kelola alamat pengiriman Anda</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('addresses.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Alamat Baru
                </a>
            </div>
        </div>
    </div>

    <!-- Addresses List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($addresses as $address)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 relative">
            <!-- Primary Badge -->
            @if($address->is_primary)
            <div class="absolute top-4 right-4">
                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                    <i class="fas fa-star mr-1"></i>
                    Utama
                </span>
            </div>
            @endif

            <!-- Address Type -->
            <div class="flex items-center mb-4">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3
                    @if($address->type === 'home') bg-blue-100 text-blue-600
                    @elseif($address->type === 'office') bg-green-100 text-green-600
                    @elseif($address->type === 'warehouse') bg-orange-100 text-orange-600
                    @else bg-gray-100 text-gray-600 @endif">
                    @if($address->type === 'home')
                        <i class="fas fa-home"></i>
                    @elseif($address->type === 'office')
                        <i class="fas fa-building"></i>
                    @elseif($address->type === 'warehouse')
                        <i class="fas fa-warehouse"></i>
                    @else
                        <i class="fas fa-map-marker-alt"></i>
                    @endif
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $address->label }}</h3>
                    <p class="text-sm text-gray-500">{{ $address->type_label }}</p>
                </div>
            </div>

            <!-- Recipient Info -->
            <div class="mb-4">
                <p class="font-medium text-gray-900">{{ $address->recipient_name }}</p>
                <p class="text-sm text-gray-600">{{ $address->phone }}</p>
            </div>

            <!-- Address Details -->
            <div class="mb-4">
                <p class="text-sm text-gray-700 leading-relaxed">{{ $address->address_line }}</p>
                <p class="text-sm text-gray-600 mt-1">
                    @if($address->district)
                        <span class="font-medium">{{ $address->district->name }}</span>,
                    @endif
                    @if($address->city)
                        <span class="font-medium">{{ $address->city->name }}</span>
                        @if($address->city->type)
                            <span class="text-xs text-gray-500">({{ $address->city->type }})</span>
                        @endif
                        ,
                    @endif
                    @if($address->province)
                        <span class="font-medium">{{ $address->province->name }}</span>
                    @endif
                    @if($address->postal_code)
                        <br><span class="text-xs text-gray-500">{{ $address->postal_code }}</span>
                    @endif
                </p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <div class="flex space-x-2">
                    @if(!$address->is_primary)
                    <form method="POST" action="{{ route('addresses.set-primary', $address) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <i class="fas fa-star mr-1"></i>
                            Jadikan Utama
                        </button>
                    </form>
                    @endif
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('addresses.edit', $address) }}"
                       class="text-gray-600 hover:text-gray-800 transition-colors">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form method="POST" action="{{ route('addresses.destroy', $address) }}" class="inline"
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus alamat ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 transition-colors">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-map-marker-alt text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada alamat</h3>
                <p class="text-gray-500 mb-6">Tambahkan alamat pengiriman untuk memudahkan proses pemesanan</p>
                <a href="{{ route('addresses.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Alamat Pertama
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Tips -->
    @if($addresses->count() > 0)
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-600 text-lg"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-900">Tips Penggunaan Alamat</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Pastikan alamat utama sudah benar untuk pengiriman default</li>
                        <li>Gunakan label yang mudah diingat (misal: "Rumah Jakarta", "Kantor Bandung")</li>
                        <li>Periksa nomor telepon penerima agar kurir dapat menghubungi</li>
                        <li>Alamat yang tidak aktif tidak akan muncul saat membuat pesanan</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
