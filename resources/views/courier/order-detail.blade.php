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

        <!-- Delivery Proof Upload -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Bukti Pengiriman</h3>

            <!-- Pickup Proof Upload -->
            <div class="mb-6 p-4 border border-gray-200 rounded-lg">
                <h4 class="text-md font-medium text-gray-900 mb-3">Bukti Pengambilan Paket</h4>
                <div id="pickup-proof-status" class="mb-3">
                    <!-- Will be populated by JavaScript -->
                </div>
                <form id="pickup-proof-form" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Foto Bukti Pengambilan
                        </label>
                        <div class="flex items-center space-x-3">
                            <input type="file" id="pickup_proof_photo" name="pickup_proof_photo"
                                   accept="image/*" capture="environment"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <button type="button" onclick="openCamera('pickup_proof_photo')"
                                    class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-camera mr-2"></i>
                                Kamera
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG. Maksimal 2MB</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Catatan (Opsional)
                        </label>
                        <textarea id="pickup-notes" name="notes" rows="2"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Tambahkan catatan jika diperlukan"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" id="pickup-submit-btn"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-upload mr-2"></i>
                            Upload Bukti Pengambilan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Delivery Proof Upload -->
            <div class="p-4 border border-gray-200 rounded-lg">
                <h4 class="text-md font-medium text-gray-900 mb-3">Bukti Pengiriman Paket</h4>
                <div id="delivery-proof-status" class="mb-3">
                    <!-- Will be populated by JavaScript -->
                </div>
                <form id="delivery-proof-form" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Foto Bukti Pengiriman
                        </label>
                        <div class="flex items-center space-x-3">
                            <input type="file" id="delivery_proof_photo" name="delivery_proof_photo"
                                   accept="image/*" capture="environment"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <button type="button" onclick="openCamera('delivery_proof_photo')"
                                    class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-camera mr-2"></i>
                                Kamera
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG. Maksimal 2MB</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Catatan (Opsional)
                        </label>
                        <textarea id="delivery-notes" name="notes" rows="2"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Tambahkan catatan jika diperlukan"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" id="delivery-submit-btn"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-upload mr-2"></i>
                            Upload Bukti Pengiriman
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Live Tracking -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Live Tracking</h3>
            <div class="mb-4">
                <a href="/orders/{{ currentOrderId }}/tracking"
                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-map-marker-alt mr-2"></i>
                    Buka Live Tracking
                </a>
            </div>
            <p class="text-sm text-gray-500">
                Aktifkan tracking untuk melacak lokasi Anda secara real-time dan memungkinkan customer melihat perjalanan pengiriman.
            </p>
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
                            <option value="failed">Gagal Kirim</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Status "Terkirim" akan otomatis berubah setelah customer mengonfirmasi penerimaan barang</p>
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

    // Update delivery proof status
    updateDeliveryProofStatus(data.order);

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
                <div class="mt-1 flex items-center space-x-2">
                    <p class="text-sm text-gray-900">${data.customer.phone || 'Tidak tersedia'}</p>
                    ${data.customer.phone ? `
                        <a href="#" onclick="openWhatsAppChat('${data.customer.phone}', '${data.order.order_number}')"
                           class="inline-flex items-center px-2 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700">
                            <i class="fab fa-whatsapp mr-1"></i> Chat
                        </a>
                    ` : ''}
                </div>
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

// Handle pickup proof form submission
document.getElementById('pickup-proof-form').addEventListener('submit', function(e) {
    e.preventDefault();
    uploadPickupProof();
});

// Handle delivery proof form submission
document.getElementById('delivery-proof-form').addEventListener('submit', function(e) {
    e.preventDefault();
    uploadDeliveryProof();
});

// Function to update delivery proof status display
function updateDeliveryProofStatus(order) {
    const pickupProofStatus = document.getElementById('pickup-proof-status');
    const deliveryProofStatus = document.getElementById('delivery-proof-status');
    const pickupForm = document.getElementById('pickup-proof-form');
    const deliveryForm = document.getElementById('delivery-proof-form');
    const pickupSubmitBtn = document.getElementById('pickup-submit-btn');
    const deliverySubmitBtn = document.getElementById('delivery-submit-btn');

    // Update pickup proof status
    if (order.pickup_proof_photo) {
        pickupProofStatus.innerHTML = `
            <div class="flex items-center p-3 bg-green-50 border border-green-200 rounded-lg">
                <i class="fas fa-check-circle text-green-600 mr-3"></i>
                <div>
                    <p class="text-sm font-medium text-green-800">Bukti pengambilan sudah diupload</p>
                    <p class="text-xs text-green-600">${order.pickup_proof_at || 'Waktu tidak tersedia'}</p>
                </div>
                <button onclick="viewProofPhoto('${order.pickup_proof_photo}', 'Bukti Pengambilan')"
                        class="ml-auto text-green-600 hover:text-green-800">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        `;
        pickupForm.classList.add('hidden');
    } else {
        pickupProofStatus.innerHTML = `
            <div class="flex items-center p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                <p class="text-sm text-yellow-800">Belum ada bukti pengambilan paket</p>
            </div>
        `;
        pickupForm.classList.remove('hidden');

        // Disable form if status doesn't allow pickup proof
        if (!['confirmed', 'assigned', 'picked_up'].includes(order.status)) {
            pickupSubmitBtn.disabled = true;
            pickupSubmitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    // Update delivery proof status
    if (order.delivery_proof_photo) {
        deliveryProofStatus.innerHTML = `
            <div class="flex items-center p-3 bg-green-50 border border-green-200 rounded-lg">
                <i class="fas fa-check-circle text-green-600 mr-3"></i>
                <div>
                    <p class="text-sm font-medium text-green-800">Bukti pengiriman sudah diupload</p>
                    <p class="text-xs text-green-600">${order.delivery_proof_at || 'Waktu tidak tersedia'}</p>
                </div>
                <button onclick="viewProofPhoto('${order.delivery_proof_photo}', 'Bukti Pengiriman')"
                        class="ml-auto text-green-600 hover:text-green-800">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        `;
        deliveryForm.classList.add('hidden');
    } else {
        deliveryProofStatus.innerHTML = `
            <div class="flex items-center p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                <p class="text-sm text-yellow-800">Belum ada bukti pengiriman paket</p>
            </div>
        `;
        deliveryForm.classList.remove('hidden');

        // Disable form if status doesn't allow delivery proof
        if (!['assigned', 'picked_up', 'in_transit'].includes(order.status)) {
            deliverySubmitBtn.disabled = true;
            deliverySubmitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }
}

// Function to open camera
function openCamera(inputId) {
    const input = document.getElementById(inputId);
    input.click();
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

// Normalize Indonesian phone numbers and open wa.me link
function openWhatsAppChat(rawPhone, orderNumber) {
    if (!rawPhone) return;
    let phone = String(rawPhone).replace(/[^0-9+]/g, '');
    // Convert leading 0 to 62, or ensure starts with 62 for ID numbers
    if (phone.startsWith('0')) {
        phone = '62' + phone.slice(1);
    } else if (phone.startsWith('+')) {
        phone = phone.slice(1);
    }

    const text = encodeURIComponent(`Halo, ini kurir untuk pesanan ${orderNumber}.`);
    const url = `https://wa.me/${phone}?text=${text}`;
    window.open(url, '_blank');
}

// Function to upload pickup proof
function uploadPickupProof() {
    const formData = new FormData();
    const photoFile = document.getElementById('pickup_proof_photo').files[0];
    const notes = document.getElementById('pickup-notes').value;

    if (!photoFile) {
        alert('Pilih foto bukti pengambilan terlebih dahulu');
        return;
    }

    formData.append('pickup_proof_photo', photoFile);
    formData.append('notes', notes);

    const submitBtn = document.getElementById('pickup-submit-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Uploading...';

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/courier/orders/${currentOrderId}/pickup-proof`, {
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
            alert('Bukti pengambilan paket berhasil diupload!');
            document.getElementById('pickup-proof-form').reset();
            loadOrderDetail(); // Reload order detail
        } else {
            alert('Gagal mengupload bukti pengambilan: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error uploading pickup proof:', error);
        alert('Terjadi kesalahan saat mengupload bukti pengambilan');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-upload mr-2"></i>Upload Bukti Pengambilan';
    });
}

// Function to upload delivery proof
function uploadDeliveryProof() {
    const formData = new FormData();
    const photoFile = document.getElementById('delivery_proof_photo').files[0];
    const notes = document.getElementById('delivery-notes').value;

    if (!photoFile) {
        alert('Pilih foto bukti pengiriman terlebih dahulu');
        return;
    }

    formData.append('delivery_proof_photo', photoFile);
    formData.append('notes', notes);

    const submitBtn = document.getElementById('delivery-submit-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Uploading...';

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/courier/orders/${currentOrderId}/delivery-proof`, {
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
            alert('Bukti pengiriman paket berhasil diupload!');
            document.getElementById('delivery-proof-form').reset();
            loadOrderDetail(); // Reload order detail
        } else {
            alert('Gagal mengupload bukti pengiriman: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error uploading delivery proof:', error);
        alert('Terjadi kesalahan saat mengupload bukti pengiriman');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-upload mr-2"></i>Upload Bukti Pengiriman';
    });
}
</script>
@endpush
@endsection
