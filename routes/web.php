<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth; // Added this import for the debug route

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');
    
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout routes - should be accessible without authentication
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Dashboard - redirect to role-specific dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile routes
    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');
    
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/password', [AuthController::class, 'updatePassword'])->name('password.update');
    
    // Debug route to test authentication
    Route::get('/debug-auth', function () {
        return response()->json([
            'authenticated' => Auth::check(),
            'user' => Auth::user(),
            'session_id' => session()->getId(),
        ]);
    })->name('debug.auth');
    
    // Orders routes - All authenticated users can view orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    
    // Customer-only routes
    Route::middleware('customer')->group(function () {
        Route::get('/orders/create', function () {
            return view('orders.create');
        })->name('orders.create');
        
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    });
    
    // Admin and Courier routes
    Route::middleware('admin.or.courier')->group(function () {
        Route::get('/orders/{order}/edit', function ($order) {
            return view('orders.edit', compact('order'));
        })->name('orders.edit');
        
        Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    });
    
    // All users can view order details and track
    Route::get('/orders/{order}', function ($order) {
        return view('orders.show', compact('order'));
    })->name('orders.show');
    
    Route::get('/orders/{order}/track', function ($order) {
        return view('orders.track', compact('order'));
    })->name('orders.track');
    
    // Complaints routes
    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    
    // Customer-only complaint routes
    Route::middleware('customer')->group(function () {
        Route::get('/complaints/create', function () {
            return view('complaints.create');
        })->name('complaints.create');
        
        Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    });
    
    // Admin routes for complaint management
    Route::middleware('admin')->group(function () {
        Route::get('/complaints/{complaint}/edit', function ($complaint) {
            return view('complaints.edit', compact('complaint'));
        })->name('complaints.edit');
        
        Route::put('/complaints/{complaint}', [ComplaintController::class, 'update'])->name('complaints.update');
    });

    // Admin routes for user management
    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/update-password', [UserController::class, 'updatePassword'])->name('users.update-password');
        Route::get('/users/statistics', [UserController::class, 'statistics'])->name('users.statistics');
    });
    
    // All users can view complaint details
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
    
    // Address routes (customer only)
    Route::middleware('customer')->group(function () {
        Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
        Route::get('/addresses/create', [AddressController::class, 'create'])->name('addresses.create');
        Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
        Route::get('/addresses/{address}/edit', [AddressController::class, 'edit'])->name('addresses.edit');
        Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
        Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
        Route::post('/addresses/{address}/set-primary', [AddressController::class, 'setPrimary'])->name('addresses.set-primary');
        
        // AJAX routes for dynamic location selection
        Route::get('/addresses/cities', [AddressController::class, 'getCities'])->name('addresses.cities');
        Route::get('/provinces', [OrderController::class, 'getProvinces'])->name('provinces.index');
        Route::get('/cities', [OrderController::class, 'getCities'])->name('cities.index');
        Route::get('/addresses/districts', [AddressController::class, 'getDistricts'])->name('addresses.districts');
    });
    
    // Role-specific dashboard routes
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->middleware('admin')->name('admin.dashboard');
    
    Route::get('/courier/dashboard', function () {
        return view('courier.dashboard');
    })->middleware('courier')->name('courier.dashboard');
    
    Route::get('/customer/dashboard', function () {
        return view('customer.dashboard');
    })->middleware('customer')->name('customer.dashboard');
});
