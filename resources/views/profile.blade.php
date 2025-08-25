@extends('layouts.app')

@section('title', 'Profile - Afiyah')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Profil Saya</h1>
                <p class="mt-2 text-gray-600">Kelola informasi profil dan akun Anda</p>
            </div>
            <a href="{{ route('dashboard') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Profile Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Profil</h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nama Lengkap
                                </label>
                                <input type="text" id="name" name="name" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                       value="{{ old('name', Auth::user()->name) }}">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Email
                                </label>
                                <input type="email" id="email" value="{{ Auth::user()->email }}" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-500" 
                                       disabled>
                                <p class="mt-1 text-sm text-gray-500">Email tidak dapat diubah</p>
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nomor Telepon
                                </label>
                                <input type="tel" id="phone" name="phone" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                       value="{{ old('phone', Auth::user()->phone) }}">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Role -->
                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                                    Jenis Akun
                                </label>
                                <input type="text" id="role" value="{{ ucfirst(Auth::user()->role->name) }}" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-500" 
                                       disabled>
                                <p class="mt-1 text-sm text-gray-500">Jenis akun tidak dapat diubah</p>
                            </div>
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                                Alamat
                            </label>
                            <textarea id="address" name="address" rows="3"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('address') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">{{ old('address', Auth::user()->address) }}</textarea>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-save mr-2"></i>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Ubah Password</h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Current Password -->
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">
                                    Password Saat Ini <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" id="current_password" name="current_password" required
                                           class="w-full border border-gray-300 rounded-lg pl-3 pr-10 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('current_password') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                                    <button type="button" onclick="togglePassword('current_password')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <i id="current-password-toggle" class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- New Password -->
                            <div>
                                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">
                                    Password Baru <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" id="new_password" name="new_password" required
                                           class="w-full border border-gray-300 rounded-lg pl-3 pr-10 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('new_password') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                                    <button type="button" onclick="togglePassword('new_password')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <i id="new-password-toggle" class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                                    </button>
                                </div>
                                @error('new_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Confirm New Password -->
                        <div>
                            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                Konfirmasi Password Baru <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="new_password_confirmation" name="new_password_confirmation" required
                                       class="w-full border border-gray-300 rounded-lg pl-3 pr-10 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <button type="button" onclick="togglePassword('new_password_confirmation')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i id="new-password-confirmation-toggle" class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-key mr-2"></i>
                                Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Profile Summary -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="text-center">
                    <div class="mx-auto h-20 w-20 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-user text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ Auth::user()->name }}</h3>
                    <p class="text-sm text-gray-500">{{ Auth::user()->email }}</p>
                    <p class="text-sm text-gray-500">{{ Auth::user()->phone }}</p>
                    <div class="mt-3">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst(Auth::user()->role->name) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Account Statistics -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik Akun</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Total Pesanan:</span>
                        <span class="text-sm font-medium">{{ $totalOrders ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Pesanan Aktif:</span>
                        <span class="text-sm font-medium">{{ $activeOrders ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Keluhan:</span>
                        <span class="text-sm font-medium">{{ $totalComplaints ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Bergabung Sejak:</span>
                        <span class="text-sm font-medium">{{ Auth::user()->created_at->format('M Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
                <div class="space-y-3">
                    @if(Auth::user()->isCustomer())
                    <a href="{{ route('orders.create') }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Buat Pesanan
                    </a>
                    @endif
                    
                    <a href="{{ route('orders.index') }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-box mr-2"></i>
                        Lihat Pesanan
                    </a>
                    
                    @if(Auth::user()->isCustomer())
                    <a href="{{ route('complaints.create') }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Laporkan Masalah
                    </a>
                    @endif
                </div>
            </div>

            <!-- Account Status -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Akun</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Status:</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            @if(Auth::user()->isActive()) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                            {{ Auth::user()->isActive() ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Email Terverifikasi:</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            Ya
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Terakhir Login:</span>
                        <span class="text-sm font-medium">{{ Auth::user()->updated_at->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const toggleId = fieldId === 'current_password' ? 'current-password-toggle' : 
                    fieldId === 'new_password' ? 'new-password-toggle' : 'new-password-confirmation-toggle';
    const passwordToggle = document.getElementById(toggleId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordToggle.classList.remove('fa-eye');
        passwordToggle.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        passwordToggle.classList.remove('fa-eye-slash');
        passwordToggle.classList.add('fa-eye');
    }
}
</script>
@endpush
@endsection
