<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\LocationTracking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TrackingController extends Controller
{
    /**
     * Show tracking page for an order
     */
    public function show(Order $order)
    {
        $user = Auth::user();

        // Check if user has access to this order
        if (!$user->isAdmin() && $order->customer_id !== $user->id && $order->courier_id !== $user->id) {
            return redirect()->route('orders.index')
                ->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk melihat tracking pesanan ini.');
        }

        // Check if order is in a trackable status
        if (!in_array($order->status, ['assigned', 'picked_up', 'in_transit', 'awaiting_confirmation'])) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Tracking tidak tersedia untuk status pesanan ini.');
        }

        $order->load(['customer', 'courier']);

        return view('tracking.show', compact('order'));
    }

    /**
     * Update user location
     */
    public function updateLocation(Request $request, Order $order): JsonResponse
    {
        // Start output buffering to catch any unexpected output from MadelineProto
        $initialObLevel = ob_get_level();
        if ($initialObLevel === 0) {
            ob_start();
        }
        
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
        ]);

        if ($validator->fails()) {
            $this->safeCleanOutputBuffers();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();

            // Check if user has access to this order
            if (!$user->isAdmin() && $order->customer_id !== $user->id && $order->courier_id !== $user->id) {
                $this->safeCleanOutputBuffers();
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                ], 403);
            }

            // Determine user type
            $userType = $user->isCourier() ? 'courier' : 'customer';

            // Save location tracking
            LocationTracking::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'user_type' => $userType,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy' => $request->accuracy,
                'speed' => $request->speed,
                'heading' => $request->heading,
                'tracked_at' => now(),
            ]);

            // Clean any output buffers that may have been created (from MadelineProto warnings)
            $this->safeCleanOutputBuffers();

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully',
            ]);

        } catch (\Exception $e) {
            // Clean output buffer on error
            $this->safeCleanOutputBuffers();
            
            \Log::error('Tracking updateLocation error: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update location: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get latest locations for an order
     */
    public function getLocations(Order $order): JsonResponse
    {
        // Start output buffering to catch any unexpected output from MadelineProto
        $initialObLevel = ob_get_level();
        if ($initialObLevel === 0) {
            ob_start();
        }
        
        try {
            $user = Auth::user();

            // Check if user has access to this order
            if (!$user->isAdmin() && $order->customer_id !== $user->id && $order->courier_id !== $user->id) {
                $this->safeCleanOutputBuffers();
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                ], 403);
            }

            // Get latest courier location (always available for all users)
            $courierLocation = LocationTracking::where('order_id', $order->id)
                ->where('user_type', 'courier')
                ->where('tracked_at', '>=', now()->subDay())
                ->latest('tracked_at')
                ->first();

            $customerLocation = null;
            $trackingHistory = collect();

            // Only show customer location and full history for courier and admin
            if ($user->isCourier() || $user->isAdmin()) {
                $customerLocation = LocationTracking::where('order_id', $order->id)
                    ->where('user_type', 'customer')
                    ->where('tracked_at', '>=', now()->subDay())
                    ->latest('tracked_at')
                    ->first();

                // Get recent tracking history (last 50 points) for courier/admin
                $trackingHistory = LocationTracking::where('order_id', $order->id)
                    ->where('tracked_at', '>=', now()->subDay())
                    ->with('user')
                    ->orderBy('tracked_at', 'desc')
                    ->limit(50)
                    ->get()
                    ->groupBy('user_type');
            } else {
                // For customer, only show courier tracking history
                $trackingHistory = LocationTracking::where('order_id', $order->id)
                    ->where('user_type', 'courier')
                    ->where('tracked_at', '>=', now()->subDay())
                    ->with('user')
                    ->orderBy('tracked_at', 'desc')
                    ->limit(50)
                    ->get()
                    ->groupBy('user_type');
            }

            // Clean any output buffers that may have been created (from MadelineProto warnings)
            $this->safeCleanOutputBuffers();

            return response()->json([
                'success' => true,
                'data' => [
                    'courier_location' => $courierLocation,
                    'customer_location' => $customerLocation,
                    'tracking_history' => $trackingHistory,
                    'user_type' => $user->isCourier() ? 'courier' : 'customer',
                ],
            ]);

        } catch (\Exception $e) {
            // Clean output buffer on error
            $this->safeCleanOutputBuffers();
            
            \Log::error('Tracking getLocations error: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get locations: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start location tracking for an order
     */
    public function startTracking(Order $order): JsonResponse
    {
        // Start output buffering to catch any unexpected output from MadelineProto
        $initialObLevel = ob_get_level();
        if ($initialObLevel === 0) {
            ob_start();
        }
        
        try {
            $user = Auth::user();

            // Check if user has access to this order
            if (!$user->isAdmin() && $order->customer_id !== $user->id && $order->courier_id !== $user->id) {
                $this->safeCleanOutputBuffers();
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                ], 403);
            }

            // Check if order is in a trackable status
            if (!in_array($order->status, ['assigned', 'picked_up', 'in_transit', 'awaiting_confirmation'])) {
                $this->safeCleanOutputBuffers();
                return response()->json([
                    'success' => false,
                    'message' => 'Order is not in a trackable status',
                ], 400);
            }

            // Clean any output buffers that may have been created (from MadelineProto warnings)
            $this->safeCleanOutputBuffers();

            return response()->json([
                'success' => true,
                'message' => 'Location tracking started',
                'data' => [
                    'order_id' => $order->id,
                    'order_status' => $order->status,
                    'user_type' => $user->isCourier() ? 'courier' : 'customer',
                ],
            ]);

        } catch (\Exception $e) {
            // Clean output buffer on error
            $this->safeCleanOutputBuffers();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to start tracking: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Safely clean all output buffers to prevent contamination of JSON responses
     */
    protected function safeCleanOutputBuffers(): void
    {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
    }
}
