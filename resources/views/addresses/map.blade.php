@extends('layouts.app')

@section('title', 'Peta Alamat - Afiyah')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/courier-map.css') }}">
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Peta Alamat Pengiriman</h1>
                <p class="mt-2 text-gray-600">Lihat semua alamat pengiriman dalam peta</p>
            </div>
            <a href="{{ route('courier.orders.index') }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Map Container -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div id="map" class="h-[600px] w-full rounded-lg"></div>
    </div>

    <!-- Address List -->
    <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Alamat</h2>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($addresses as $address)
            <div class="p-6 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $address->label }}</h3>
                        <p class="mt-1 text-gray-600">{{ $address->recipient_name }} - {{ $address->phone }}</p>
                        <p class="mt-1 text-gray-600">{{ $address->address_line }}</p>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $address->district->name ?? '' }}, 
                            {{ $address->city->name }}, 
                            {{ $address->province->name }}
                            @if($address->postal_code)
                                {{ $address->postal_code }}
                            @endif
                        </p>
                    </div>
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $address->latitude }},{{ $address->longitude }}"
                       target="_blank"
                       class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-directions mr-1"></i>Petunjuk Arah
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/courier-map.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addresses = @json($addresses);
    initCourierMap('map', addresses);
});
</script>
@endpush
