<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WaybillController;
use App\Models\Order as OrderModel;
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
    Route::middleware('admin_or_courier')->group(function () {
        Route::get('/orders/{order}/edit', function (OrderModel $order) {
            return view('orders.show', compact('order'));
        })->name('orders.edit');

        Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    });

    // All users can view order details
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Only admin can track orders
    // Route track dihapus - fungsionalitas dipindah ke show.blade.php

    // Admin order confirmation
    Route::post('/orders/{order}/confirm', [OrderController::class, 'confirmOrder'])->name('orders.confirm');

    // Admin waybill & tracking view
    Route::get('/orders/{order}/waybill', [OrderController::class, 'waybill'])->name('orders.waybill');

    // Waybill management routes
    Route::prefix('waybill')->name('waybill.')->middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/calculate', [WaybillController::class, 'calculate'])->name('calculate');
        Route::get('/search', [WaybillController::class, 'search'])->name('search');
        Route::post('/store', [WaybillController::class, 'store'])->name('store');
        Route::put('/{order}/cancel', [WaybillController::class, 'cancel'])->name('cancel');
        Route::get('/{order}/detail', [WaybillController::class, 'detail'])->name('detail');
        Route::get('/{order}/history', [WaybillController::class, 'history'])->name('history');
        Route::post('/{order}/pickup', [WaybillController::class, 'pickup'])->name('pickup');
        Route::post('/{order}/print-label', [WaybillController::class, 'printLabel'])->name('print-label');
    });





    // Complaints routes
    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');

    // Customer-only complaint routes
    Route::middleware('customer')->group(function () {
        Route::get('/complaints/create', function () {
            return view('complaints.create');
        })->name('complaints.create');

        Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    });

    // Complaint edit routes (accessible to admin and complaint owner)
    Route::get('/complaints/{complaint}/edit', [ComplaintController::class, 'edit'])->name('complaints.edit');
    Route::put('/complaints/{complaint}', [ComplaintController::class, 'update'])->name('complaints.update');

    // Admin complaint close route
    Route::post('/complaints/{complaint}/close', [ComplaintController::class, 'closeComplaint'])->name('complaints.close');

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
    Route::get('/admin/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->middleware('admin')->name('admin.dashboard');
    Route::get('/admin/dashboard/data', [App\Http\Controllers\AdminController::class, 'getDashboardData'])->middleware('admin')->name('admin.dashboard.data');

    // Admin courier bank info management routes
    Route::get('/admin/courier-bank-info', [App\Http\Controllers\AdminController::class, 'courierBankInfo'])->middleware('admin')->name('admin.courier-bank-info');
    Route::post('/admin/courier-bank-info', [App\Http\Controllers\AdminController::class, 'storeCourierBankInfo'])->middleware('admin')->name('admin.courier-bank-info.store');
    Route::get('/admin/courier-bank-info/{id}', [App\Http\Controllers\AdminController::class, 'showCourierBankInfo'])->middleware('admin')->name('admin.courier-bank-info.show');
    Route::post('/admin/courier-bank-info/{id}', [App\Http\Controllers\AdminController::class, 'updateCourierBankInfo'])->middleware('admin')->name('admin.courier-bank-info.update');
    Route::post('/admin/courier-bank-info/{id}/toggle-status', [App\Http\Controllers\AdminController::class, 'toggleCourierBankStatus'])->middleware('admin')->name('admin.courier-bank-info.toggle-status');

    Route::get('/courier/dashboard', [App\Http\Controllers\CourierController::class, 'dashboard'])->middleware('courier')->name('courier.dashboard');
    Route::get('/courier/dashboard/data', [App\Http\Controllers\CourierController::class, 'getDashboardData'])->middleware('courier')->name('courier.dashboard.data');

    // Courier order management routes
    Route::get('/courier/orders', function() {
        return view('courier.orders');
    })->middleware('courier')->name('courier.orders.index');
    Route::get('/courier/orders/{order}/detail', function($order) {
        return view('courier.order-detail', compact('order'));
    })->middleware('courier')->name('courier.orders.show');
    Route::get('/courier/orders/{order}/api', [App\Http\Controllers\CourierController::class, 'getOrderDetails'])->middleware('courier')->name('courier.orders.api');
    Route::put('/courier/orders/{order}/status', [App\Http\Controllers\CourierController::class, 'updateOrderStatus'])->middleware('courier')->name('courier.orders.update-status');

    // Courier delivery proof upload routes
    Route::post('/courier/orders/{order}/pickup-proof', [App\Http\Controllers\CourierController::class, 'uploadPickupProof'])->middleware('courier')->name('courier.orders.upload-pickup-proof');
    Route::post('/courier/orders/{order}/delivery-proof', [App\Http\Controllers\CourierController::class, 'uploadDeliveryProof'])->middleware('courier')->name('courier.orders.upload-delivery-proof');

    // Courier pricing management routes
    Route::get('/courier/pricing', [App\Http\Controllers\CourierController::class, 'pricing'])->middleware('courier')->name('courier.pricing');
    Route::post('/courier/pricing', [App\Http\Controllers\CourierController::class, 'storePricing'])->middleware('courier')->name('courier.pricing.store');
    Route::put('/courier/pricing', [App\Http\Controllers\CourierController::class, 'updatePricing'])->middleware('courier')->name('courier.pricing.update');
    Route::get('/courier/pricing/data', [App\Http\Controllers\CourierController::class, 'getPricingData'])->middleware('courier')->name('courier.pricing.data');
    Route::post('/courier/pricing/calculate', [App\Http\Controllers\CourierController::class, 'calculateFee'])->middleware('courier')->name('courier.pricing.calculate');

    Route::get('/customer/dashboard', [App\Http\Controllers\CustomerController::class, 'dashboard'])->middleware('customer')->name('customer.dashboard');
    Route::get('/customer/dashboard/data', [App\Http\Controllers\CustomerController::class, 'getDashboardData'])->middleware('customer')->name('customer.dashboard.data');
    
    // Customer delivery confirmation route
    Route::post('/orders/{order}/confirm-delivery', [App\Http\Controllers\CustomerController::class, 'confirmDelivery'])->middleware('customer')->name('orders.confirm-delivery');

    // Tracking routes
    Route::get('/orders/{order}/tracking', [App\Http\Controllers\TrackingController::class, 'show'])->name('orders.tracking');
    Route::post('/orders/{order}/tracking/location', [App\Http\Controllers\TrackingController::class, 'updateLocation'])->name('orders.tracking.location');
    Route::get('/orders/{order}/tracking/locations', [App\Http\Controllers\TrackingController::class, 'getLocations'])->name('orders.tracking.locations');
    Route::post('/orders/{order}/tracking/start', [App\Http\Controllers\TrackingController::class, 'startTracking'])->name('orders.tracking.start');

    // Available couriers for manual delivery (accessible to all authenticated users)
    Route::get('/available-couriers', [OrderController::class, 'getAvailableCouriers'])->name('available.couriers');

    // Debug route for testing
    Route::get('/debug-couriers', function() {
        try {
            $couriers = App\Models\User::getActiveCouriersWithPricing();
            return response()->json([
                'success' => true,
                'count' => $couriers->count(),
                'data' => $couriers->map(function($c) {
                    return [
                        'id' => $c->id,
                        'name' => $c->name,
                        'has_pricing' => $c->courierPricing ? true : false,
                        'pricing_data' => $c->courierPricing ? [
                            'base_fee' => $c->courierPricing->base_fee,
                            'per_kg_fee' => $c->courierPricing->per_kg_fee,
                        ] : null
                    ];
                })
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    })->name('debug.couriers');
});
