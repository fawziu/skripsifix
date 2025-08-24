@extends('layouts.app')

@section('title', 'Detail Pesanan - Kurir')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Detail Pesanan</h1>
                <p class="mt-2 text-gray-600">Informasi lengkap pesanan yang ditugaskan</p>
            </div>
            <a href="{{ route('courier.orders.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loading-state" class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-600">Memuat detail pesanan...</span>
        </div>
    </div>

    <!-- Error State -->
    <div id="error-state" class="hidden bg-white rounded-lg shadow p-6">
        <div class="text-center">
            <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Gagal Memuat Detail</h3>
            <p class="text-gray-600 mb-4" id="error-message">Terjadi kesalahan saat memuat detail pesanan.</p>
            <button onclick="loadOrderDetail()"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-refresh mr-2"></i>
                Coba Lagi
            </button>
        </div>
    </div>

    <!-- Order Detail Content -->
    <div id="order-detail-content" class="hidden space-y-6">
        <!-- Order Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pesanan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="order-info">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>

        <!-- Customer Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Customer</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="customer-info">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>

        <!-- Status History -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Status</h3>
            <div id="status-history" class="space-y-4">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>

        <!-- Update Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Update Status</h3>
            <form id="status-update-form">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status Baru
                        </label>
                        <select id="new-status" name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Status</option>
                            <option value="picked_up">Barang Diambil</option>
                            <option value="in_transit">Dalam Perjalanan</option>
                            <option value="delivered">Terkirim</option>
                            <option value="failed">Gagal Kirim</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Catatan (Opsional)
                        </label>
                        <textarea id="status-notes" name="notes" rows="3"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Tambahkan catatan jika diperlukan"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentOrderId = null;

// Load order detail on page load
document.addEventListener('DOMContentLoaded', function() {
    // Extract order ID from URL
    const pathParts = window.location.pathname.split('/');
    currentOrderId = pathParts[pathParts.length - 2]; // /courier/orders/{id}/detail
    loadOrderDetail();
});

function loadOrderDetail() {
    const loadingState = document.getElementById('loading-state');
    const errorState = document.getElementById('error-state');
    const orderDetailContent = document.getElementById('order-detail-content');

    // Show loading
    loadingState.classList.remove('hidden');
    errorState.classList.add('hidden');
    orderDetailContent.classList.add('hidden');

    fetch(`/courier/orders/${currentOrderId}/api`)
        .then(response => response.json())
        .then(data => {
            loadingState.classList.add('hidden');

            if (data.success) {
                displayOrderDetail(data.data);
            } else {
                showError(data.message || 'Gagal memuat detail pesanan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            loadingState.classList.add('hidden');
            showError('Terjadi kesalahan saat memuat detail pesanan');
        });
}

function displayOrderDetail(data) {
    const orderDetailContent = document.getElementById('order-detail-content');
    const orderInfo = document.getElementById('order-info');
    const customerInfo = document.getElementById('customer-info');
    const statusHistory = document.getElementById('status-history');

    // Display order info
    orderInfo.innerHTML = `
        <div>
            <span class="text-sm font-medium text-gray-600">Order Number:</span>
            <p class="text-sm text-gray-900 mt-1">${data.order.order_number}</p>
        </div>
        <div>
            <span class="text-sm font-medium text-gray-600">Status:</span>
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-1 ${getStatusClass(data.order.status)}">
                ${getStatusText(data.order.status)}
            </span>
        </div>
        <div>
            <span class="text-sm font-medium text-gray-600">Item Description:</span>
            <p class="text-sm text-gray-900 mt-1">${data.order.item_description}</p>
        </div>
        <div>
            <span class="text-sm font-medium text-gray-600">Weight:</span>
            <p class="text-sm text-gray-900 mt-1">${data.order.item_weight} kg</p>
        </div>
        <div>
            <span class="text-sm font-medium text-gray-600">Origin Address:</span>
            <p class="text-sm text-gray-900 mt-1">${data.order.origin_address}</p>
        </div>
        <div>
            <span class="text-sm font-medium text-gray-600">Destination Address:</span>
            <p class="text-sm text-gray-900 mt-1">${data.order.destination_address}</p>
        </div>
        <div>
            <span class="text-sm font-medium text-gray-600">Shipping Cost:</span>
            <p class="text-sm text-gray-900 mt-1">Rp ${parseInt(data.order.shipping_cost).toLocaleString()}</p>
        </div>
        <div>
            <span class="text-sm font-medium text-gray-600">Total Amount:</span>
            <p class="text-sm text-gray-900 mt-1">Rp ${parseInt(data.order.total_amount).toLocaleString()}</p>
        </div>
    `;

    // Display customer info
    if (data.customer) {
        customerInfo.innerHTML = `
            <div>
                <span class="text-sm font-medium text-gray-600">Name:</span>
                <p class="text-sm text-gray-900 mt-1">${data.customer.name}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-600">Phone:</span>
                <p class="text-sm text-gray-900 mt-1">${data.customer.phone || 'Tidak tersedia'}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-600">Email:</span>
                <p class="text-sm text-gray-900 mt-1">${data.customer.email}</p>
            </div>
        `;
    } else {
        customerInfo.innerHTML = '<p class="text-gray-500">Informasi customer tidak tersedia</p>';
    }

    // Display status history
    if (data.status_history && data.status_history.length > 0) {
        statusHistory.innerHTML = data.status_history.map((status, index) => `
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-blue-600 text-sm"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">${getStatusText(status.status)}</p>
                    <p class="text-sm text-gray-500">${status.updated_by}</p>
                    <p class="text-sm text-gray-500">${status.created_at}</p>
                    ${status.notes ? `<p class="text-sm text-gray-600 mt-1">${status.notes}</p>` : ''}
                </div>
            </div>
            ${index < data.status_history.length - 1 ? '<div class="ml-4 border-l-2 border-gray-200 h-4"></div>' : ''}
        `).join('');
    } else {
        statusHistory.innerHTML = '<p class="text-gray-500">Belum ada riwayat status</p>';
    }

    orderDetailContent.classList.remove('hidden');
}

function showError(message) {
    const errorState = document.getElementById('error-state');
    const errorMessage = document.getElementById('error-message');

    errorMessage.textContent = message;
    errorState.classList.remove('hidden');
}

// Get status class for styling
function getStatusClass(status) {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'confirmed': 'bg-blue-100 text-blue-800',
        'assigned': 'bg-purple-100 text-purple-800',
        'picked_up': 'bg-orange-100 text-orange-800',
        'in_transit': 'bg-indigo-100 text-indigo-800',
        'delivered': 'bg-green-100 text-green-800',
        'failed': 'bg-red-100 text-red-800',
        'cancelled': 'bg-gray-100 text-gray-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

// Get status text
function getStatusText(status) {
    const texts = {
        'pending': 'Menunggu',
        'confirmed': 'Dikonfirmasi',
        'assigned': 'Ditugaskan',
        'picked_up': 'Diambil',
        'in_transit': 'Dalam Perjalanan',
        'delivered': 'Terkirim',
        'failed': 'Gagal',
        'cancelled': 'Dibatalkan'
    };
    return texts[status] || status;
}

// Handle status update form submission
document.getElementById('status-update-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const status = document.getElementById('new-status').value;
    const notes = document.getElementById('status-notes').value;

    if (!status) {
        alert('Pilih status terlebih dahulu');
        return;
    }

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/courier/orders/${currentOrderId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            status: status,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Status berhasil diupdate');
            document.getElementById('status-update-form').reset();
            loadOrderDetail(); // Reload order detail
        } else {
            alert('Gagal mengupdate status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error updating status:', error);
        alert('Terjadi kesalahan saat mengupdate status');
    });
});
</script>
@endpush
@endsection
