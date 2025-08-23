@extends('layouts.app')

@section('title', 'Admin Dashboard - Afiyah')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                <p class="mt-2 text-gray-600">Selamat datang, {{ Auth::user()->name }}! Kelola sistem Afiyah Anda.</p>
            </div>
            <div class="text-right">
                <div class="flex items-center justify-end mb-2">
                    <div class="flex items-center text-green-600 text-sm mr-4">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>
                        <span>Real-time aktif</span>
                    </div>
                    <div class="flex items-center text-blue-600 text-sm">
                        <i class="fas fa-clock mr-1"></i>
                        <span>WITA</span>
                    </div>
                </div>
                <p class="text-sm text-gray-500">Update terakhir:</p>
                <p class="text-sm font-medium text-gray-700" id="last-update">{{ now()->format('H:i:s') }} WITA</p>
                <button onclick="updateDashboardData()" class="mt-2 px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition-colors">
                    <i class="fas fa-sync-alt mr-1"></i>Refresh
                </button>
            </div>
        </div>
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
                    <p class="text-2xl font-bold text-gray-900" data-stat="total-orders">{{ $totalOrders ?? 0 }}</p>
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
                    <p class="text-2xl font-bold text-gray-900" data-stat="pending-orders">{{ $pendingOrders ?? 0 }}</p>
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
                    <p class="text-2xl font-bold text-gray-900" data-stat="active-couriers">{{ $activeCouriers ?? 0 }}</p>
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
                    <p class="text-2xl font-bold text-gray-900" data-stat="open-complaints">{{ $openComplaints ?? 0 }}</p>
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
                    <div id="recent-orders-container" class="space-y-4">
                        @foreach($recentOrders as $order)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">#{{ $order->id }}</p>
                                <p class="text-sm text-gray-500">{{ Str::limit($order->item_description, 30) }}</p>
                                <p class="text-xs text-gray-400">{{ $order->customer->name ?? 'Unknown Customer' }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                                    @elseif($order->status === 'delivered') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                                <p class="text-sm text-gray-500 mt-1">{{ $order->created_at->format('d M Y H:i') }} WITA</p>
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
                    <div id="recent-orders-container" class="text-center py-8">
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
                    <div id="recent-complaints-container" class="space-y-4">
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
                                <p class="text-sm text-gray-500 mt-1">{{ $complaint->created_at->format('d M Y H:i') }} WITA</p>
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
                    <div id="recent-complaints-container" class="text-center py-8">
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

<!-- Real-time Data Update Script -->
<script>
// Global function for manual refresh
function updateDashboardData() {
    // Show loading state
    const refreshBtn = document.querySelector('button[onclick="updateDashboardData()"]');
    const originalText = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Loading...';
    refreshBtn.disabled = true;

    fetch('{{ route("admin.dashboard.data") }}')
        .then(response => response.json())
        .then(data => {
            // Update statistics cards
            document.querySelector('[data-stat="total-orders"]').textContent = data.totalOrders;
            document.querySelector('[data-stat="pending-orders"]').textContent = data.pendingOrders;
            document.querySelector('[data-stat="active-couriers"]').textContent = data.activeCouriers;
            document.querySelector('[data-stat="open-complaints"]').textContent = data.openComplaints;

            // Update recent orders
            updateRecentOrders(data.recentOrders);

            // Update recent complaints
            updateRecentComplaints(data.recentComplaints);

                        // Update last refresh time
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                timeZone: 'Asia/Makassar',
                hour12: false
            });
            document.getElementById('last-update').textContent = timeString + ' WITA';

            // Show success notification
            showNotification('Data dashboard berhasil diperbarui!', 'success');

            // Restore button state
            refreshBtn.innerHTML = originalText;
            refreshBtn.disabled = false;
        })
        .catch(error => {
            console.error('Error updating dashboard data:', error);
            // Restore button state on error
            refreshBtn.innerHTML = originalText;
            refreshBtn.disabled = false;
        });
}

document.addEventListener('DOMContentLoaded', function() {

    // Function to update recent orders
    function updateRecentOrders(orders) {
        const ordersContainer = document.getElementById('recent-orders-container');
        if (!ordersContainer) return;

        if (orders.length === 0) {
            ordersContainer.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-box text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500">Belum ada pesanan</p>
                </div>
            `;
            return;
        }

        let ordersHTML = '<div class="space-y-4">';
        orders.forEach(order => {
            const statusClass = getStatusClass(order.status);
            ordersHTML += `
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">#${order.id}</p>
                        <p class="text-sm text-gray-500">${order.item_description ? order.item_description.substring(0, 30) + (order.item_description.length > 30 ? '...' : '') : 'No description'}</p>
                        <p class="text-xs text-gray-400">${order.customer ? order.customer.name : 'Unknown Customer'}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                            ${order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                        </span>
                        <p class="text-sm text-gray-500 mt-1">${new Date(order.created_at).toLocaleDateString('id-ID')} ${new Date(order.created_at).toLocaleTimeString('id-ID', {hour12: false})} WITA</p>
                    </div>
                </div>
            `;
        });
        ordersHTML += '</div>';

        ordersContainer.innerHTML = ordersHTML;
    }

    // Function to update recent complaints
    function updateRecentComplaints(complaints) {
        const complaintsContainer = document.getElementById('recent-complaints-container');
        if (!complaintsContainer) return;

        if (complaints.length === 0) {
            complaintsContainer.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500">Belum ada komplain</p>
                </div>
            `;
            return;
        }

        let complaintsHTML = '<div class="space-y-4">';
        complaints.forEach(complaint => {
            const priorityClass = getPriorityClass(complaint.priority);
            complaintsHTML += `
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">${complaint.title}</p>
                        <p class="text-sm text-gray-500">${complaint.user ? complaint.user.name : 'Unknown User'}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${priorityClass}">
                            ${complaint.priority.charAt(0).toUpperCase() + complaint.priority.slice(1)}
                        </span>
                        <p class="text-sm text-gray-500 mt-1">${new Date(complaint.created_at).toLocaleDateString('id-ID')} ${new Date(complaint.created_at).toLocaleTimeString('id-ID', {hour12: false})} WITA</p>
                    </div>
                </div>
            `;
        });
        complaintsHTML += '</div>';

        complaintsContainer.innerHTML = complaintsHTML;
    }

    // Helper function to get status class
    function getStatusClass(status) {
        switch(status) {
            case 'pending': return 'bg-yellow-100 text-yellow-800';
            case 'confirmed': return 'bg-blue-100 text-blue-800';
            case 'delivered': return 'bg-green-100 text-green-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }

    // Helper function to get priority class
    function getPriorityClass(priority) {
        switch(priority) {
            case 'urgent': return 'bg-red-100 text-red-800';
            case 'high': return 'bg-orange-100 text-orange-800';
            case 'medium': return 'bg-yellow-100 text-yellow-800';
            default: return 'bg-green-100 text-green-800';
        }
    }

        // Update data every 30 seconds
    setInterval(updateDashboardData, 30000);

    // Initial update
    updateDashboardData();

    // Function to show notifications
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} mr-2"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        // Add to page
        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }, 3000);
    }
});
</script>
@endsection
