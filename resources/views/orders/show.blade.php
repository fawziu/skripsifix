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
                        @elseif($order->status === 'awaiting_confirmation') bg-yellow-100 text-yellow-800
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
                            'awaiting_confirmation' => ['icon' => 'clock', 'title' => 'Awaiting Confirmation', 'description' => 'Menunggu konfirmasi customer'],
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
                        @if($order->originCity)
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $order->originCity->name }}, {{ $order->originCity->province->name }}
                            </p>
                        @endif
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-2">Alamat Tujuan</h4>
                        <p class="text-sm text-gray-900">{{ $order->destination_address }}</p>
                        @if($order->destinationCity)
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $order->destinationCity->name }}, {{ $order->destinationCity->province->name }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Delivery/Receipt Proof -->
            @if($order->pickup_proof_photo || $order->delivery_proof_photo)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Bukti Pengiriman / Penerimaan</h3>

                @if($order->pickup_proof_photo)
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-600 mb-2">Bukti Pengambilan Paket</h4>
                    <div class="flex items-center space-x-3">
                        <img src="{{ asset('storage/' . $order->pickup_proof_photo) }}"
                             alt="Bukti Pengambilan"
                             class="w-24 h-24 object-cover rounded-lg border border-gray-200 cursor-pointer"
                             onclick="viewProofPhoto('{{ $order->pickup_proof_photo }}', 'Bukti Pengambilan Paket')">
                        <div>
                            <p class="text-sm text-gray-900">Foto bukti pengambilan paket</p>
                            @if($order->pickup_proof_at)
                                <p class="text-xs text-gray-500">Diambil pada: {{ $order->pickup_proof_at->format('d M Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                @if($order->delivery_proof_photo)
                <div>
                    <h4 class="text-sm font-medium text-gray-600 mb-2">Bukti Penerimaan Paket</h4>
                    <div class="flex items-center space-x-3">
                        <img src="{{ asset('storage/' . $order->delivery_proof_photo) }}"
                             alt="Bukti Penerimaan"
                             class="w-24 h-24 object-cover rounded-lg border border-gray-200 cursor-pointer"
                             onclick="viewProofPhoto('{{ $order->delivery_proof_photo }}', 'Bukti Penerimaan Paket')">
                        <div>
                            <p class="text-sm text-gray-900">Foto bukti penerimaan paket</p>
                            @if($order->delivery_proof_at)
                                <p class="text-xs text-gray-500">Dikirim pada: {{ $order->delivery_proof_at->format('d M Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Payment Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Pembayaran</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-2">Metode Pembayaran</h4>
                        <div class="flex items-center space-x-2">
                            @if($order->payment_method === 'cod')
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    <i class="fas fa-money-bill-wave mr-1"></i>COD (Cash on Delivery)
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    <i class="fas fa-university mr-1"></i>Transfer Bank
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            @if($order->payment_method === 'cod')
                                Pembayaran dilakukan saat barang diterima
                            @else
                                Transfer ke rekening kurir sebelum pengiriman
                            @endif
                        </p>
                    </div>
                    @if($order->payment_method === 'transfer' && $order->courier && $order->courier->courierPricing && $order->courier->courierPricing->bank_info)
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-2">Informasi Rekening Kurir</h4>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-sm font-medium text-gray-900">{{ $order->courier->courierPricing->bank_info['bank_name'] }}</p>
                            <p class="text-sm text-gray-600 font-mono">{{ $order->courier->courierPricing->bank_info['account_number'] }}</p>
                            <p class="text-sm text-gray-600">{{ $order->courier->courierPricing->bank_info['account_holder'] }}</p>
                        </div>
                    </div>
                    @endif
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
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                            <p class="text-sm text-blue-700">
                                <strong>Admin:</strong> Anda hanya dapat mengkonfirmasi pesanan dan request pickup.
                                Status pengiriman akan diupdate oleh kurir.
                            </p>
                        </div>
                    </div>
                    @endif
                                        @if(Auth::user()->isAdmin())
                    <!-- Admin Tracking Section -->
                    <div class="border-b border-gray-200 pb-3 mb-3">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Tracking & Monitoring</h4>
                        <div class="space-y-2">
                            @if($order->shipping_method === 'rajaongkir' && $order->tracking_number)
                            <a href="{{ route('orders.waybill', $order) }}"
                               class="w-full inline-flex items-center justify-center px-3 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-receipt mr-2"></i>
                                Lihat Waybill & Tracking
                            </a>
                            @endif

                            @if($order->shipping_method === 'manual' || !$order->tracking_number)
                            <button type="button" onclick="showManualTracking()"
                                   class="w-full inline-flex items-center justify-center px-3 py-2 bg-gray-600 text-white text-xs font-medium rounded-lg hover:bg-gray-700 transition-colors">
                                <i class="fas fa-info-circle mr-2"></i>
                                Status Manual
                            </button>
                            @endif
                        </div>
                    </div>
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

                    {{-- Button Tugaskan Kurir dan Update Status dihilangkan untuk admin --}}
                    {{-- Admin hanya bisa melakukan konfirmasi pesanan dan request pickup --}}

                    @if(Auth::user()->isCustomer() && Auth::id() === $order->customer_id)
                        @if(in_array($order->status, ['in_transit','awaiting_confirmation']))
                        <button type="button" onclick="confirmDelivery()"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors mb-3">
                            <i class="fas fa-check-circle mr-2"></i>
                            Konfirmasi Barang Diterima
                        </button>

                        <div class="bg-white rounded-lg border border-gray-200 p-4 mb-3">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Upload Bukti Penerimaan</h4>
                            <form id="customer-receipt-proof-form" class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Foto Bukti Penerimaan</label>
                                    <div class="flex items-center space-x-3">
                                        <input type="file" id="receipt_proof_photo" name="receipt_proof_photo" accept="image/*" capture="environment"
                                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        <button type="button" onclick="document.getElementById('receipt_proof_photo').click()"
                                                class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                            <i class="fas fa-camera mr-2"></i>Kamera
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG. Maksimal 2MB</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                                    <textarea id="receipt-notes" name="notes" rows="2"
                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="Tambahkan catatan jika diperlukan"></textarea>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" id="receipt-submit-btn"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                        <i class="fas fa-upload mr-2"></i>Upload Bukti Penerimaan
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif
                        
                        <a href="{{ route('complaints.create', ['order_id' => $order->id]) }}"
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Laporkan Masalah
                        </a>
                    @endif

                    @if(in_array($order->status, ['assigned', 'picked_up', 'in_transit', 'awaiting_confirmation']))
                    <a href="{{ route('orders.tracking', $order) }}"
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        Live Tracking
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Tracking Modal -->
    @if(Auth::user()->isAdmin())
    <div id="admin-tracking-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Tracking Information</h3>
                    <button onclick="closeAdminTrackingModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div id="admin-tracking-content">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Admin Status Update Modal dihilangkan --}}
    {{-- Admin tidak lagi bisa mengupdate status secara manual --}}
</div>

@push('scripts')
<script>
function confirmOrder() {
    if (confirm('Apakah Anda yakin ingin mengkonfirmasi pesanan ini?')) {
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(`/orders/{{ $order->id }}/confirm`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let message = 'Pesanan berhasil dikonfirmasi';

                // Show WhatsApp notification link if available
                if (data.whatsapp_link) {
                    message += '\n\nKirim notifikasi WhatsApp ke customer?';
                    if (confirm(message)) {
                        window.open(data.whatsapp_link, '_blank');
                    }
                } else {
                    alert(message);
                }

                // Reload page to show updated status
                window.location.reload();
            } else {
                alert('Gagal mengkonfirmasi pesanan: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengkonfirmasi pesanan');
        });
    }
}

// Function assignCourier dihilangkan karena admin tidak lagi bisa menugaskan kurir secara manual

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

// Admin Status Modal Functions dihilangkan karena admin tidak lagi bisa mengupdate status secara manual

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

// Admin tracking functions
function loadAdminTracking() {
    const modal = document.getElementById('admin-tracking-modal');
    const content = document.getElementById('admin-tracking-content');

    content.innerHTML = `
        <div class="flex items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-600">Memuat informasi tracking...</span>
        </div>
    `;

    modal.classList.remove('hidden');

    // Load tracking data
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
            displayAdminTracking(data.data);
        } else {
            showAdminTrackingError(data.message || 'Gagal memuat informasi tracking');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAdminTrackingError('Terjadi kesalahan saat memuat informasi tracking');
    });
}

function displayAdminTracking(trackingData) {
    const content = document.getElementById('admin-tracking-content');

    let html = `
        <div class="space-y-6">
            <!-- Shipment Info -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-3">Informasi Pengiriman</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
    `;

    if (trackingData.summary) {
        const summary = trackingData.summary;
        html += `
            <div><span class="font-medium">Status:</span> ${summary.status || 'N/A'}</div>
            <div><span class="font-medium">Kurir:</span> ${summary.courier_name || 'N/A'}</div>
            <div><span class="font-medium">Asal:</span> ${summary.origin || 'N/A'}</div>
            <div><span class="font-medium">Tujuan:</span> ${summary.destination || 'N/A'}</div>
            <div><span class="font-medium">Estimasi:</span> ${summary.etd || 'N/A'}</div>
            <div><span class="font-medium">Berat:</span> ${summary.weight || 'N/A'} kg</div>
        `;
    }

    html += `
                </div>
            </div>

            <!-- Tracking Timeline -->
            <div>
                <h4 class="font-medium text-gray-900 mb-3">Timeline Pengiriman</h4>
    `;

    if (trackingData.manifest && trackingData.manifest.length > 0) {
        html += '<div class="space-y-4">';
        trackingData.manifest.forEach((item, index) => {
            html += `
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-truck text-blue-600 text-xs"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">${item.manifest_description || 'Update Status'}</p>
                        <div class="flex items-center space-x-4 mt-1 text-xs text-gray-500">
                            <span><i class="fas fa-calendar mr-1"></i>${item.manifest_date || 'N/A'}</span>
                            <span><i class="fas fa-clock mr-1"></i>${item.manifest_time || 'N/A'}</span>
                        </div>
                        ${item.city_name ? `<p class="text-xs text-gray-600 mt-1">${item.city_name}</p>` : ''}
                    </div>
                </div>
                ${index < trackingData.manifest.length - 1 ? '<div class="ml-3 border-l-2 border-gray-200 h-4"></div>' : ''}
            `;
        });
        html += '</div>';
    } else {
        html += `
            <div class="text-center text-gray-500 py-4">
                <i class="fas fa-info-circle text-2xl mb-2"></i>
                <p>Belum ada informasi tracking yang tersedia</p>
            </div>
        `;
    }

    html += `
            </div>
        </div>
    `;

    content.innerHTML = html;
}

function showAdminTrackingError(message) {
    const content = document.getElementById('admin-tracking-content');
    content.innerHTML = `
        <div class="text-center text-red-500 py-8">
            <i class="fas fa-exclamation-triangle text-3xl mb-3"></i>
            <p class="text-lg font-medium">${message}</p>
        </div>
    `;
}

function showManualTracking() {
    const modal = document.getElementById('admin-tracking-modal');
    const content = document.getElementById('admin-tracking-content');

    const html = `
        <div class="space-y-6">
            <div class="text-center">
                <i class="fas fa-truck text-blue-500 text-4xl mb-4"></i>
                <h4 class="text-lg font-medium text-gray-900 mb-2">Status Pengiriman Manual</h4>
                <p class="text-gray-600">Pesanan ini menggunakan metode pengiriman manual</p>
            </div>

            <!-- Order Status Timeline -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h5 class="font-medium text-gray-900 mb-3">Status Pesanan</h5>
                <div class="space-y-3">
                    @php
                        $statuses = [
                            'pending' => ['icon' => 'clock', 'color' => 'yellow', 'label' => 'Menunggu Konfirmasi'],
                            'confirmed' => ['icon' => 'check-circle', 'color' => 'blue', 'label' => 'Dikonfirmasi'],
                            'assigned' => ['icon' => 'user-tie', 'color' => 'indigo', 'label' => 'Ditugaskan ke Kurir'],
                            'picked_up' => ['icon' => 'truck', 'color' => 'purple', 'label' => 'Diambil Kurir'],
                            'in_transit' => ['icon' => 'route', 'color' => 'orange', 'label' => 'Dalam Perjalanan'],
                            'awaiting_confirmation' => ['icon' => 'clock', 'color' => 'yellow', 'label' => 'Menunggu Konfirmasi'],
                            'delivered' => ['icon' => 'check-double', 'color' => 'green', 'label' => 'Terkirim']
                        ];
                    @endphp

                    @foreach($statuses as $status => $info)
                        @php
                            $isActive = $order->status === $status;
                            $isCompleted = array_search($order->status, array_keys($statuses)) >= array_search($status, array_keys($statuses));
                            $iconColor = $isActive ? $info['color'] : ($isCompleted ? 'green' : 'gray');
                            $bgColor = $isActive ? $info['color'] : ($isCompleted ? 'green' : 'gray');
                        @endphp

                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-6 h-6 bg-{{ $bgColor }}-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-{{ $info['icon'] }} text-{{ $iconColor }}-600 text-xs"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $info['label'] }}</p>
                                @if($isActive)
                                    <p class="text-xs text-{{ $bgColor }}-600 font-medium">Status Saat Ini</p>
                                @endif
                            </div>
                            @if($isCompleted)
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check text-green-500 text-xs"></i>
                                </div>
                            @endif
                        </div>
                        @if(!$loop->last)
                            <div class="ml-3 border-l-2 border-gray-200 h-3"></div>
                        @endif
                    @endforeach
                </div>
            </div>

            @if($order->courier)
            <div class="bg-blue-50 rounded-lg p-4">
                <h5 class="font-medium text-blue-900 mb-2">Informasi Kurir</h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-blue-700">Nama Kurir:</span>
                        <p class="text-blue-900 font-medium">{{ $order->courier->name }}</p>
                    </div>
                    <div>
                        <span class="text-blue-700">Status:</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            @if($order->status === 'assigned') bg-blue-100 text-blue-800
                            @elseif($order->status === 'picked_up') bg-purple-100 text-purple-800
                            @elseif($order->status === 'in_transit') bg-orange-100 text-orange-800
                            @elseif($order->status === 'delivered') bg-green-100 text-green-800
                            @elseif($order->status === 'awaiting_confirmation') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                        </span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    `;

    content.innerHTML = html;
    modal.classList.remove('hidden');
}

function closeAdminTrackingModal() {
    document.getElementById('admin-tracking-modal').classList.add('hidden');
}

// Update confirmOrder function to use new API
function confirmOrder() {
    if (confirm('Apakah Anda yakin ingin mengkonfirmasi pesanan ini?')) {
        fetch(`{{ route('orders.confirm', $order) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.data && data.data.waybill_number) {
                    alert(`Pesanan berhasil dikonfirmasi!\n\nWaybill Number: ${data.data.waybill_number}\n\nTracking URL: ${data.data.tracking_url || 'N/A'}`);
                } else {
                    alert('Pesanan berhasil dikonfirmasi untuk pengiriman manual!');
                }
                // Reload page to show updated status
                location.reload();
            } else {
                alert('Gagal mengkonfirmasi pesanan: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengkonfirmasi pesanan');
        });
    }
}

// Function to confirm delivery by customer
function confirmDelivery() {
    if (confirm('Apakah Anda yakin barang telah diterima dengan baik? Setelah dikonfirmasi, pesanan akan ditandai sebagai selesai.')) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const formEl = document.getElementById('customer-receipt-proof-form');
        const fileInput = document.getElementById('receipt_proof_photo');
        const notesInput = document.getElementById('receipt-notes');

        const formData = new FormData();
        if (fileInput && fileInput.files[0]) {
            formData.append('delivery_proof_photo', fileInput.files[0]);
        }
        if (notesInput && notesInput.value) {
            formData.append('notes', notesInput.value);
        }

        fetch(`/orders/{{ $order->id }}/confirm-delivery`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let message = 'Terima kasih! Pesanan telah dikonfirmasi sebagai diterima.';

                // Show WhatsApp notification link if available
                if (data.whatsapp_link) {
                    message += '\n\nKirim notifikasi WhatsApp ke kurir?';
                    if (confirm(message)) {
                        window.open(data.whatsapp_link, '_blank');
                    }
                } else {
                    alert(message);
                }

                // Reload page to show updated status
                window.location.reload();
            } else {
                alert('Gagal mengonfirmasi penerimaan: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengonfirmasi penerimaan');
        });
    }
}

// Function to view proof photo
function viewProofPhoto(photoPath, title) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">${title}</h3>
                <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="text-center">
                <img src="/storage/${photoPath}" alt="${title}" class="max-w-full h-auto rounded-lg">
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
}
</script>
@endpush
@endsection
