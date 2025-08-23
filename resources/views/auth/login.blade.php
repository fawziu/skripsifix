@extends('layouts.app')

@section('title', 'Login - Afiyah')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-blue-100">
                <i class="fas fa-truck text-blue-600 text-xl"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Masuk ke Akun Anda
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Atau
                <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                    daftar akun baru
                </a>
            </p>
        </div>

        <!-- Login Form -->
        <form class="mt-8 space-y-6" method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="space-y-4">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input id="email" name="email" type="email" required 
                               class="appearance-none relative block w-full pl-10 pr-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm @error('email') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                               placeholder="Masukkan email Anda"
                               value="{{ old('email') }}">
                    </div>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="password" name="password" type="password" required 
                               class="appearance-none relative block w-full pl-10 pr-10 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm @error('password') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                               placeholder="Masukkan password Anda">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i id="password-toggle" class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" 
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-900">
                            Ingat saya
                        </label>
                    </div>

                    @if (Route::has('password.request'))
                        <div class="text-sm">
                            <a href="{{ route('password.request') }}" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                                Lupa password?
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-sign-in-alt text-blue-500 group-hover:text-blue-400"></i>
                    </span>
                    Masuk
                </button>
            </div>

            <!-- Daftar akun baru -->
            <div class="mt-6 text-center">
                <span class="text-gray-600 text-sm">Belum punya akun?</span>
                <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:text-blue-500 transition-colors ml-1">
                    Daftar akun
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('password-toggle');
    
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
