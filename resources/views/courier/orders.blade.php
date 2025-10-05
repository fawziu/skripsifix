@extends('layouts.app')

@section('title', 'Daftar Pesanan - Kurir')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Daftar Pesanan</h1>
                <p class="mt-2 text-gray-600">Kelola pesanan yang telah ditugaskan kepada Anda</p>
            </div>
            <a href="{{ route('courier.dashboard') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-box text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Pesanan</p>
                    <p class="text-2xl font-semibold text-gray-900" id="total-orders">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Dalam Proses</p>
                    <p class="text-2xl font-semibold text-gray-900" id="in-progress">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Selesai Hari Ini</p>
                    <p class="text-2xl font-semibold text-gray-900" id="completed-today">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-trophy text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Selesai</p>
                    <p class="text-2xl font-semibold text-gray-900" id="total-completed">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Pesanan Aktif</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            No. Pesanan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Customer
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Barang
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Alamat Tujuan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="orders-table-body">
                    <!-- Orders will be loaded here -->
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200">
            <div class="text-center text-gray-500" id="no-orders" style="display: none;">
                Tidak ada pesanan aktif saat ini
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="status-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Update Status Pesanan</h3>
            <form id="status-form">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Status Baru
                    </label>
                    <select id="new-status" name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Status</option>
                        <option value="picked_up">Barang Diambil</option>
                        <option value="in_transit">Dalam Perjalanan</option>
                        <option value="awaiting_confirmation">Menunggu Konfirmasi</option>
                        <option value="failed">Gagal Kirim</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan (Opsional)
                    </label>
                    <textarea id="status-notes" name="notes" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Tambahkan catatan jika diperlukan"></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeStatusModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
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

// Load orders data
function loadOrders() {
    fetch('/courier/dashboard/data')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatistics(data.data);
                updateOrdersTable(data.data.currentDeliveries);
            } else {
                console.error('Failed to load orders:', data.message);
                showError('Gagal memuat data pesanan: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error loading orders:', error);
            showError('Terjadi kesalahan saat memuat data pesanan');
        });
}

// Show error message
function showError(message) {
    const noOrders = document.getElementById('no-orders');
    noOrders.innerHTML = `<div class="text-red-600">${message}</div>`;
    noOrders.style.display = 'block';
}

// Update statistics
function updateStatistics(data) {
    document.getElementById('total-orders').textContent = data.assignedOrders;
    document.getElementById('in-progress').textContent = data.inProgressOrders;
    document.getElementById('completed-today').textContent = data.completedToday;
    document.getElementById('total-completed').textContent = data.totalDelivered;
}

// Update orders table
function updateOrdersTable(orders) {
    const tbody = document.getElementById('orders-table-body');
    const noOrders = document.getElementById('no-orders');

    if (orders.length === 0) {
        tbody.innerHTML = '';
        noOrders.style.display = 'block';
        return;
    }

    noOrders.style.display = 'none';

    tbody.innerHTML = orders.map(order => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                ${order.order_number || order.id}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${order.customer_name}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${order.item_description}
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">
                <div class="max-w-xs truncate">
                    ${order.destination_address}
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusClass(order.status)}">
                    ${getStatusText(order.status)}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${order.created_at}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button onclick="viewOrderDetails(${order.id})"
                        class="text-blue-600 hover:text-blue-900 mr-3">
                    Detail
                </button>
                ${['assigned', 'picked_up', 'in_transit', 'awaiting_confirmation'].includes(order.status) ? 
                    `<a href="/orders/${order.id}/tracking" class="text-green-600 hover:text-green-900 mr-3">
                        Live Tracking
                    </a>` : ''
                }
                <button onclick="openStatusModal(${order.id})"
                        class="text-green-600 hover:text-green-900">
                    Update Status
                </button>
            </td>
        </tr>
    `).join('');
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
        'awaiting_confirmation': 'bg-yellow-100 text-yellow-800',
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
        'awaiting_confirmation': 'Menunggu Konfirmasi',
        'failed': 'Gagal',
        'cancelled': 'Dibatalkan'
    };
    return texts[status] || status;
}

// Open status update modal
function openStatusModal(orderId) {
    currentOrderId = orderId;
    document.getElementById('status-modal').classList.remove('hidden');
}

// Close status update modal
function closeStatusModal() {
    document.getElementById('status-modal').classList.add('hidden');
    document.getElementById('status-form').reset();
    currentOrderId = null;
}

// View order details
function viewOrderDetails(orderId) {
    window.open(`/courier/orders/${orderId}/detail`, '_blank');
}

// Handle status form submission
document.getElementById('status-form').addEventListener('submit', function(e) {
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
            let message = 'Status berhasil diupdate';

            // Show WhatsApp notification link if available
            if (data.whatsapp_link) {
                message += '\n\nKirim notifikasi WhatsApp ke customer?';
                if (confirm(message)) {
                    window.open(data.whatsapp_link, '_blank');
                }
            } else {
                alert(message);
            }

            closeStatusModal();
            loadOrders(); // Reload orders
        } else {
            alert('Gagal mengupdate status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error updating status:', error);
        alert('Terjadi kesalahan saat mengupdate status');
    });
});

// Load orders on page load
document.addEventListener('DOMContentLoaded', function() {
    loadOrders();

    // Auto-refresh every 30 seconds
    setInterval(loadOrders, 30000);
});
</script>
@endpush
@endsection
