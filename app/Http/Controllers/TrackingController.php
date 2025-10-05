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

        $order->load(['customer', 'courier']);

        return view('tracking.show', compact('order'));
    }

    /**
     * Update user location
     */
    public function updateLocation(Request $request, Order $order): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
        ]);

        if ($validator->fails()) {
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

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully',
            ]);

        } catch (\Exception $e) {
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
        try {
            $user = Auth::user();

            // Check if user has access to this order
            if (!$user->isAdmin() && $order->customer_id !== $user->id && $order->courier_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                ], 403);
            }

            // Get latest locations for both courier and customer
            $courierLocation = LocationTracking::where('order_id', $order->id)
                ->courier()
                ->recent()
                ->latest('tracked_at')
                ->first();

            $customerLocation = LocationTracking::where('order_id', $order->id)
                ->customer()
                ->recent()
                ->latest('tracked_at')
                ->first();

            // Get recent tracking history (last 50 points)
            $trackingHistory = LocationTracking::where('order_id', $order->id)
                ->recent()
                ->with('user')
                ->orderBy('tracked_at', 'desc')
                ->limit(50)
                ->get()
                ->groupBy('user_type');

            return response()->json([
                'success' => true,
                'data' => [
                    'courier_location' => $courierLocation,
                    'customer_location' => $customerLocation,
                    'tracking_history' => $trackingHistory,
                ],
            ]);

        } catch (\Exception $e) {
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
        try {
            $user = Auth::user();

            // Check if user has access to this order
            if (!$user->isAdmin() && $order->customer_id !== $user->id && $order->courier_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                ], 403);
            }

            // Check if order is in a trackable status
            if (!in_array($order->status, ['assigned', 'picked_up', 'in_transit', 'awaiting_confirmation'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is not in a trackable status',
                ], 400);
            }

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
            return response()->json([
                'success' => false,
                'message' => 'Failed to start tracking: ' . $e->getMessage(),
            ], 500);
        }
    }
}
