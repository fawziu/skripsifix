<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use App\Services\RajaOngkirService;
use App\Services\LocalGeographicalService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    private OrderService $orderService;
    private RajaOngkirService $rajaOngkirService;
    private LocalGeographicalService $localGeographicalService;

    public function __construct(OrderService $orderService, RajaOngkirService $rajaOngkirService, LocalGeographicalService $localGeographicalService)
    {
        $this->orderService = $orderService;
        $this->rajaOngkirService = $rajaOngkirService;
        $this->localGeographicalService = $localGeographicalService;
    }

    /**
     * Get all orders (admin) or user's orders
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $filters = $request->only(['status', 'shipping_method', 'date_from', 'date_to', 'sort']);

            $orders = $this->orderService->getOrdersByUser($user, $filters);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $orders,
                ]);
            }

            // For web requests, return view with orders data
            return view('orders.index', compact('orders'));

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get orders',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to load orders. Please try again.');
        }
    }

    /**
     * Create a new order
     */
    public function store(Request $request)
    {
        // Debug: Log the incoming request data
        Log::info('Order creation request received', [
            'all_data' => $request->all(),
            'user_id' => $request->user() ? $request->user()->id : null,
        ]);

        $validator = Validator::make($request->all(), [
            'item_description' => 'required|string',
            'item_weight' => 'required|numeric|min:0.1',
            'item_price' => 'required|numeric|min:0',
            'shipping_method' => 'required|in:manual,rajaongkir',
            'origin_address' => 'required|string',
            'destination_address_id' => 'nullable|exists:addresses,id',
            'destination_address' => 'nullable|string',
            'origin_province' => 'required_if:shipping_method,rajaongkir|integer',
            'origin_city' => 'required_if:shipping_method,rajaongkir|integer',
            'courier_service' => 'sometimes|string',
            'shipping_cost' => 'required|numeric|min:0',
            'service_fee' => 'required|numeric|min:0',
            'total_cost' => 'required|numeric|min:0',
        ]);

        // Additional validation for RajaOngkir shipping method
        if ($request->shipping_method === 'rajaongkir') {
            if (!$request->origin_province || !$request->origin_city) {
                $validator->errors()->add('shipping_method', 'Provinsi dan kota asal diperlukan untuk pengiriman via RajaOngkir.');
            }
        }

        if ($validator->fails()) {
            Log::error('Order validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input_data' => $request->all(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = $request->user();

            if (!$user->isCustomer()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only customers can create orders',
                    ], 403);
                }

                return redirect()->back()->with('error', 'Hanya customer yang dapat membuat pesanan.');
            }

            // Get destination address with relationships loaded
            $destinationAddress = null;
            if ($request->destination_address_id) {
                $destinationAddress = $user->addresses()->with(['province', 'city', 'district'])->findOrFail($request->destination_address_id);
            }

            // Prepare order data
            $orderData = [
                'item_description' => $request->item_description,
                'item_weight' => $request->item_weight,
                'item_price' => $request->item_price,
                'shipping_method' => $request->shipping_method,
                'origin_address' => $request->origin_address,
                'destination_address' => $destinationAddress ? $destinationAddress->full_address : $request->destination_address,
                'origin_province' => $request->origin_province,
                'origin_city' => $request->origin_city,
                'destination_province' => $destinationAddress && $destinationAddress->province ? $destinationAddress->province->rajaongkir_id : null,
                'destination_city' => $destinationAddress && $destinationAddress->city ? $destinationAddress->city->rajaongkir_id : null,
                'courier_service' => $request->courier_service,
                'shipping_cost' => $request->shipping_cost,
                'service_fee' => $request->service_fee,
                'total_cost' => $request->total_cost,
            ];

            // Log the order data for debugging (remove in production)
            Log::info('Order data being sent to service:', $orderData);

            $order = $this->orderService->createOrder($orderData, $user);

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully',
                    'data' => $order->load(['customer', 'courier', 'admin']),
                ], 201);
            }

            return redirect()->route('orders.index')
                ->with('success', 'Pesanan berhasil dibuat!');
        } catch (\Exception $e) {
            Log::error('Error creating order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create order',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Gagal membuat pesanan. Silakan coba lagi.')
                ->withInput();
        }
    }

    /**
     * Get order details
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user has access to this order
            if (!$user->isAdmin() && $order->customer_id !== $user->id && $order->courier_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                ], 403);
            }

            $order->load(['customer', 'courier', 'admin', 'statusHistory.updatedBy']);

            return response()->json([
                'success' => true,
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get order details',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search destinations using Direct Search Method
     */
    public function searchDestinations(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required|string|min:2',
            'limit' => 'sometimes|integer|min:1|max:50',
            'offset' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $destinations = $this->rajaOngkirService->searchDestinations(
                $request->search,
                $request->limit ?? 10,
                $request->offset ?? 0
            );

            return response()->json([
                'success' => true,
                'data' => $destinations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search destinations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate shipping cost using RajaOngkir
     */
    public function calculateShippingCost(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'origin' => 'required|integer',
            'destination' => 'required|integer',
            'weight' => 'required|numeric|min:0.1',
            'courier' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->orderService->calculateRajaOngkirCost($request->all());

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate shipping cost',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate shipping cost for order form
     */
    public function calculateShipping(Request $request): JsonResponse
    {
        // Debug: Check authentication
        Log::info('Calculate shipping request', [
            'user' => $request->user() ? $request->user()->id : 'not_authenticated',
            'request_data' => $request->all(),
        ]);

        $validator = Validator::make($request->all(), [
            'origin' => 'required|integer',
            'destination' => 'required|integer',
            'weight' => 'required|numeric|min:0.1',
            'courier' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::error('Calculate shipping validation failed', [
                'errors' => $validator->errors()->toArray(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Prepare data for RajaOngkir API
            $costData = [
                'origin' => $request->origin,
                'destination' => $request->destination,
                'weight' => $request->weight * 1000, // Convert to grams
                'courier' => $request->courier,
            ];

            Log::info('Calling RajaOngkir API', $costData);

            $result = $this->rajaOngkirService->calculateShippingCost($costData);

            Log::info('RajaOngkir API result', [
                'result_count' => count($result),
                'result' => $result,
            ]);

            if (empty($result)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to calculate shipping cost from RajaOngkir',
                    'data' => []
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Error calculating shipping cost', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate shipping cost',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Confirm order (admin only)
     */
    public function confirmOrder(Request $request, Order $order): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tracking_number' => 'sometimes|string',
            'courier_service' => 'sometimes|string',
            'estimated_delivery' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();

            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can confirm orders',
                ], 403);
            }

            $success = $this->orderService->confirmOrder($order, $user, $request->all());

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order confirmed successfully',
                    'data' => $order->load(['customer', 'courier', 'admin']),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm order',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign courier to order (admin only)
     */
    public function assignCourier(Request $request, Order $order): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'courier_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();

            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can assign couriers',
                ], 403);
            }

            $courier = User::find($request->courier_id);

            if (!$courier->isCourier()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected user is not a courier',
                ], 400);
            }

            $success = $this->orderService->assignCourier($order, $courier, $user);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Courier assigned successfully',
                    'data' => $order->load(['customer', 'courier', 'admin']),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign courier',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign courier',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,assigned,picked_up,in_transit,delivered,cancelled',
            'notes' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();

            // Check if user has permission to update this order
            if (!$user->isAdmin() && $order->courier_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                ], 403);
            }

            $success = $this->orderService->updateOrderStatus(
                $order,
                $request->status,
                $user,
                $request->notes
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order status updated successfully',
                    'data' => $order->load(['customer', 'courier', 'admin']),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Track order
     */
    public function trackOrder(Request $request, Order $order): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user has access to this order
            if (!$user->isAdmin() && $order->customer_id !== $user->id && $order->courier_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                ], 403);
            }

            $result = $this->orderService->trackOrder($order);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get local provinces (Sulawesi Selatan and Papua Barat)
     */
    public function getProvinces(): JsonResponse
    {
        try {
            $provinces = $this->localGeographicalService->getProvinces();

            return response()->json([
                'success' => true,
                'data' => $provinces,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get provinces',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get local cities by province
     */
    public function getCities(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'province_id' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $cities = $this->localGeographicalService->getCities($request->province_id);

            return response()->json([
                'success' => true,
                'data' => $cities,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cities',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available couriers
     */
    public function getCouriers(): JsonResponse
    {
        try {
            $couriers = $this->rajaOngkirService->getAvailableCouriers();

            return response()->json([
                'success' => true,
                'data' => $couriers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get couriers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate shipping label for order
     */
    public function generateLabel(Request $request, Order $order): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user has access to this order
            if (!$user->isAdmin() && $order->customer_id !== $user->id && $order->courier_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                ], 403);
            }

            $result = $this->orderService->getShippingLabel($order);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate label',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cached destinations for specific provinces
     */
    public function getCachedDestinations(): JsonResponse
    {
        try {
            $destinations = $this->localGeographicalService->getCachedDestinationsForProvinces();

            return response()->json([
                'success' => true,
                'data' => $destinations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cached destinations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
