@extends('layouts.app')

@section('title', 'Kelola Informasi Bank Kurir - Afiyah')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Kelola Informasi Bank Kurir</h1>
                <p class="mt-2 text-gray-600">Kelola informasi rekening bank untuk pembayaran transfer kurir</p>
            </div>
            <a href="{{ route('admin.dashboard') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Courier Bank Info List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Daftar Informasi Bank Kurir</h3>
                <button type="button" onclick="openAddModal()"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Informasi Bank
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kurir
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nama Bank
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nomor Rekening
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Atas Nama
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($courierPricings as $pricing)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <i class="fas fa-user text-blue-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $pricing->courier->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $pricing->courier->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">
                                    {{ $pricing->bank_info['bank_name'] ?? 'Belum diatur' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-mono text-gray-900">
                                    {{ $pricing->bank_info['account_number'] ?? 'Belum diatur' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">
                                    {{ $pricing->bank_info['account_holder'] ?? 'Belum diatur' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($pricing->bank_info && ($pricing->bank_info['is_active'] ?? false))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times mr-1"></i>Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button type="button" onclick="openEditModal({{ $pricing->id }})"
                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" onclick="toggleBankStatus({{ $pricing->id }})"
                                        class="text-{{ $pricing->bank_info && ($pricing->bank_info['is_active'] ?? false) ? 'red' : 'green' }}-600 hover:text-{{ $pricing->bank_info && ($pricing->bank_info['is_active'] ?? false) ? 'red' : 'green' }}-900">
                                    <i class="fas fa-{{ $pricing->bank_info && ($pricing->bank_info['is_active'] ?? false) ? 'ban' : 'check' }}"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                Tidak ada data kurir dengan informasi bank
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="bankModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Tambah Informasi Bank</h3>
                <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="bankForm" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="courier_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Pilih Kurir <span class="text-red-500">*</span>
                        </label>
                        <select id="courier_id" name="courier_id" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih kurir</option>
                            @foreach($couriers as $courier)
                                <option value="{{ $courier->id }}">{{ $courier->name }} ({{ $courier->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Bank <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="bank_name" name="bank_name" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Contoh: Bank Central Asia (BCA)">
                    </div>

                    <div>
                        <label for="account_number" class="block text-sm font-medium text-gray-700 mb-1">
                            Nomor Rekening <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="account_number" name="account_number" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Contoh: 1234567890">
                    </div>

                    <div>
                        <label for="account_holder" class="block text-sm font-medium text-gray-700 mb-1">
                            Atas Nama <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="account_holder" name="account_holder" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Nama pemilik rekening">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Aktifkan informasi bank
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let editingPricingId = null;

function openAddModal() {
    editingPricingId = null;
    document.getElementById('modalTitle').textContent = 'Tambah Informasi Bank';
    document.getElementById('bankForm').reset();
    document.getElementById('bankForm').action = '{{ route("admin.courier-bank-info.store") }}';
    document.getElementById('bankForm').method = 'POST';
    document.getElementById('courier_id').disabled = false;
    document.getElementById('bankModal').classList.remove('hidden');
}

function openEditModal(pricingId) {
    editingPricingId = pricingId;
    document.getElementById('modalTitle').textContent = 'Edit Informasi Bank';
    document.getElementById('bankForm').action = `{{ route("admin.courier-bank-info.update", "") }}/${pricingId}`;
    document.getElementById('bankForm').method = 'POST';
    document.getElementById('courier_id').disabled = true;

    // Load existing data
    fetch(`{{ route("admin.courier-bank-info.show", "") }}/${pricingId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const pricing = data.data;
                document.getElementById('courier_id').value = pricing.courier_id;
                document.getElementById('bank_name').value = pricing.bank_info.bank_name || '';
                document.getElementById('account_number').value = pricing.bank_info.account_number || '';
                document.getElementById('account_holder').value = pricing.bank_info.account_holder || '';
                document.getElementById('is_active').checked = pricing.bank_info.is_active || false;
            }
        });

    document.getElementById('bankModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('bankModal').classList.add('hidden');
}

function toggleBankStatus(pricingId) {
    if (confirm('Apakah Anda yakin ingin mengubah status informasi bank ini?')) {
        fetch(`{{ route("admin.courier-bank-info.toggle-status", "") }}/${pricingId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal mengubah status: ' + data.message);
            }
        });
    }
}

// Close modal when clicking outside
document.getElementById('bankModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endpush
@endsection
