<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Logistics Management System')</title>
    
    <!-- Web App Manifest -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Afiyah">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                        <i class="fas fa-truck text-blue-600 text-xl"></i>
                        <span class="font-bold text-xl text-gray-900">Afiyah</span>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            <i class="fas fa-home mr-2"></i>Dashboard
                        </a>
                        
                        @if(Auth::user()->isCustomer())
                            <a href="{{ route('orders.create') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                <i class="fas fa-plus mr-2"></i>Buat Pesanan
                            </a>
                            <a href="{{ route('addresses.index') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                <i class="fas fa-map-marker-alt mr-2"></i>Alamat
                            </a>
                        @endif
                        
                        <a href="{{ route('orders.index') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            <i class="fas fa-box mr-2"></i>Orders
                        </a>
                        <a href="{{ route('complaints.index') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Complaints
                        </a>
                        
                        <!-- User Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                <i class="fas fa-user-circle text-lg"></i>
                                <span>{{ Auth::user()->name }}</span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i>Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
                            Register
                        </a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-700 hover:text-blue-600 p-2">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden border-t border-gray-200">
            <div class="px-2 pt-2 pb-3 space-y-1">
                @auth
                    <a href="{{ route('dashboard') }}" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md text-base font-medium">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                    
                    @if(Auth::user()->isCustomer())
                        <a href="{{ route('orders.create') }}" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md text-base font-medium">
                            <i class="fas fa-plus mr-2"></i>Buat Pesanan
                        </a>
                        <a href="{{ route('addresses.index') }}" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md text-base font-medium">
                            <i class="fas fa-map-marker-alt mr-2"></i>Alamat
                        </a>
                    @endif
                    
                    <a href="{{ route('orders.index') }}" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md text-base font-medium">
                        <i class="fas fa-box mr-2"></i>Orders
                    </a>
                    <a href="{{ route('complaints.index') }}" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md text-base font-medium">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Complaints
                    </a>
                    <a href="{{ route('profile') }}" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md text-base font-medium">
                        <i class="fas fa-user mr-2"></i>Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md text-base font-medium">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md text-base font-medium">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md text-base font-medium">
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="min-h-screen">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="text-center text-gray-500 text-sm">
                <p>&copy; {{ date('Y') }} Logistics Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Flash Messages -->
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition class="fixed top-4 right-4 z-50">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>{{ session('success') }}</span>
                    <button @click="show = false" class="ml-4 text-green-700 hover:text-green-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition class="fixed top-4 right-4 z-50">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span>{{ session('error') }}</span>
                    <button @click="show = false" class="ml-4 text-red-700 hover:text-red-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @stack('scripts')
</body>
</html>
