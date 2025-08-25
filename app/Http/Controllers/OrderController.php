<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use App\Services\RajaOngkirService;
use App\Services\LocalGeographicalService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    private OrderService $orderService;
    private RajaOngkirService $rajaOngkirService;
    private LocalGeographicalService $localGeographicalService;
    private WhatsAppNotificationService $whatsappService;

    public function __construct(OrderService $orderService, RajaOngkirService $rajaOngkirService, LocalGeographicalService $localGeographicalService, WhatsAppNotificationService $whatsappService)
    {
        $this->orderService = $orderService;
        $this->rajaOngkirService = $rajaOngkirService;
        $this->localGeographicalService = $localGeographicalService;
        $this->whatsappService = $whatsappService;
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
            'payment_method' => 'required_if:shipping_method,manual|in:cod,transfer',
            'origin_address' => 'required|string',
            'destination_address_id' => 'nullable|exists:addresses,id',
            'destination_address' => 'nullable|string',
            'origin_province' => 'nullable|integer',
            'origin_city' => 'nullable|integer',
            'courier_service' => 'nullable|string',
            'courier_id' => 'nullable|exists:users,id',
            'courier_pricing_id' => 'nullable|exists:courier_pricing,id',
            'shipping_cost' => 'required|numeric|min:0',
            'service_fee' => 'required|numeric|min:0',
            'total_cost' => 'required|numeric|min:0',
        ]);

        // Additional validation for RajaOngkir shipping method
        if ($request->shipping_method === 'rajaongkir') {
            if (!$request->origin_province || !$request->origin_city) {
                $validator->errors()->add('shipping_method', 'Provinsi dan kota asal diperlukan untuk pengiriman via RajaOngkir.');
            }
            if (!$request->courier_service) {
                $validator->errors()->add('courier_service', 'Pilih layanan kurir untuk pengiriman via RajaOngkir.');
            }
        }

        // Additional validation for manual shipping method
        if ($request->shipping_method === 'manual') {
            if (!$request->courier_id || !$request->courier_pricing_id) {
                $validator->errors()->add('shipping_method', 'Pilih kurir untuk pengiriman manual.');
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
                'payment_method' => $request->shipping_method === 'manual' ? $request->payment_method : null,
                'origin_address' => $request->origin_address,
                'destination_address' => $destinationAddress ? $destinationAddress->full_address : $request->destination_address,
                'origin_province' => $request->origin_province,
                'origin_city' => $request->origin_city,
                'destination_province' => $destinationAddress && $destinationAddress->province ? $destinationAddress->province->rajaongkir_id : null,
                'destination_city' => $destinationAddress && $destinationAddress->city ? $destinationAddress->city->rajaongkir_id : null,
                'courier_service' => $request->courier_service,
                'courier_id' => $request->shipping_method === 'manual' ? $request->courier_id : null,
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

    // Method track dihapus - fungsionalitas dipindah ke show.blade.php

    /**
     * Show waybill and tracking page for admin
     */
    public function waybill(Request $request, Order $order)
    {
        try {
            $user = $request->user();

            // Check if user is admin
            if (!$user->isAdmin()) {
                return redirect()->route('orders.index')
                    ->with('error', 'Akses ditolak. Hanya admin yang dapat mengakses halaman waybill.');
            }

            // Load necessary relationships for waybill
            $order->load(['customer', 'courier', 'statusHistory.updatedBy', 'originCity.province', 'destinationCity.province']);

            return view('orders.waybill', compact('order'));

        } catch (\Exception $e) {
            return redirect()->route('orders.index')
                ->with('error', 'Gagal memuat halaman waybill. Silakan coba lagi.');
        }
    }

    /**
     * Confirm order and generate waybill
     */
    public function confirmOrder(Request $request, Order $order): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user is admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only admin can confirm orders.',
                ], 403);
            }

            // Check if order can be confirmed
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be confirmed. Current status: ' . $order->status,
                ], 400);
            }

            // If shipping method is RajaOngkir, generate waybill
            if ($order->shipping_method === 'rajaongkir') {
                try {
                    $waybillData = $this->rajaOngkirService->createWaybill([
                        'origin' => $order->origin_city,
                        'destination' => $order->destination_city,
                        'weight' => $order->item_weight,
                        'courier' => $order->courier_service,
                        'description' => $order->item_description,
                        'value' => $order->item_price,
                        'origin_address' => $order->origin_address,
                        'destination_address' => $order->destination_address,
                        'customer_name' => $order->customer->name,
                        'customer_phone' => $order->customer->phone,
                    ]);

                    if ($waybillData && isset($waybillData['waybill_number'])) {
                        $order->update([
                            'status' => 'confirmed',
                            'tracking_number' => $waybillData['waybill_number'],
                            'waybill_data' => json_encode($waybillData),
                            'confirmed_at' => now(),
                            'confirmed_by' => $user->id,
                        ]);

                        // Create status history
                        $order->statusHistory()->create([
                            'status' => 'confirmed',
                            'notes' => 'Pesanan dikonfirmasi dan waybill RajaOngkir dibuat: ' . $waybillData['waybill_number'],
                            'updated_by' => $user->id,
                        ]);

                        // Generate WhatsApp notification link
                        $whatsappLink = $this->whatsappService->generateOrderConfirmationLink($order, $user);

                        return response()->json([
                            'success' => true,
                            'message' => 'Order confirmed and waybill generated successfully',
                            'data' => [
                                'waybill_number' => $waybillData['waybill_number'],
                                'tracking_url' => $waybillData['tracking_url'] ?? null,
                                'label_url' => $waybillData['label_url'] ?? null,
                            ],
                            'whatsapp_link' => $whatsappLink,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to generate RajaOngkir waybill', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to generate RajaOngkir waybill: ' . $e->getMessage(),
                    ], 500);
                }
            } else {
                // For manual shipping, just confirm the order
                $order->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                    'confirmed_by' => $user->id,
                ]);

                // Create status history
                $order->statusHistory()->create([
                    'status' => 'confirmed',
                    'notes' => 'Pesanan dikonfirmasi untuk pengiriman manual',
                    'updated_by' => $user->id,
                ]);

                // Generate WhatsApp notification link
                $whatsappLink = $this->whatsappService->generateOrderConfirmationLink($order, $user);

                return response()->json([
                    'success' => true,
                    'message' => 'Order confirmed for manual shipping',
                    'whatsapp_link' => $whatsappLink,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to confirm order', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get order details
     */
    public function show(Request $request, Order $order)
    {
        try {
            $user = $request->user();

            // Check if user has access to this order
            if (!$user->isAdmin() && $order->customer_id !== $user->id && $order->courier_id !== $user->id) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied',
                    ], 403);
                }

                return redirect()->route('orders.index')
                    ->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk melihat pesanan ini.');
            }

            $order->load(['customer', 'courier', 'admin', 'statusHistory.updatedBy', 'originCity.province', 'destinationCity.province']);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $order,
                ]);
            }

            // Return view for web requests
            return view('orders.show', compact('order'));

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get order details',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return redirect()->route('orders.index')
                ->with('error', 'Gagal memuat detail pesanan. Silakan coba lagi.');
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

    // Method confirmOrder lama dihapus - diganti dengan yang baru di atas

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

            $result = $this->orderService->updateOrderStatus(
                $order,
                $request->status,
                $user,
                $request->notes
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order status updated successfully',
                    'data' => $order->load(['customer', 'courier', 'admin']),
                    'whatsapp_link' => $result['whatsapp_link'],
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

    /**
     * Get available couriers with pricing for manual delivery
     */
    public function getAvailableCouriers(): JsonResponse
    {
        try {
            Log::info('getAvailableCouriers called');

            $couriers = User::getActiveCouriersWithPricing();

            Log::info('Couriers found:', ['count' => $couriers->count()]);

            $courierData = $couriers->map(function ($courier) {
                return [
                    'id' => $courier->id,
                    'name' => $courier->name,
                    'phone' => $courier->phone,
                    'base_fee' => $courier->courierPricing->base_fee,
                    'per_kg_fee' => $courier->courierPricing->per_kg_fee,
                    'pricing_id' => $courier->courierPricing->id,
                    'bank_info' => $courier->courierPricing->bank_info,
                ];
            });

            Log::info('Courier data prepared:', ['data' => $courierData->toArray()]);

            return response()->json([
                'success' => true,
                'data' => $courierData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getAvailableCouriers:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get available couriers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
