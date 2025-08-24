<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WaybillController extends Controller
{
    private RajaOngkirService $rajaOngkirService;

    public function __construct(RajaOngkirService $rajaOngkirService)
    {
        $this->rajaOngkirService = $rajaOngkirService;
    }

    /**
     * Calculate shipping cost
     */
    public function calculate(Request $request): JsonResponse
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
            $result = $this->rajaOngkirService->calculate($request->all());

            if ($result) {
                return response()->json([
                    'success' => true,
                    'data' => $result,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate shipping cost',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Waybill calculate error', [
                'message' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search waybills
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'waybill_number' => 'sometimes|string',
            'courier' => 'sometimes|string',
            'status' => 'sometimes|string',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->rajaOngkirService->search($request->all());

            if ($result) {
                return response()->json([
                    'success' => true,
                    'data' => $result,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No waybills found',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Waybill search error', [
                'message' => $e->getMessage(),
                'filters' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store waybill
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'courier' => 'required|string',
            'service' => 'required|string',
            'weight' => 'required|numeric|min:0.1',
            'origin' => 'required|integer',
            'destination' => 'required|integer',
            'description' => 'required|string',
            'value' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $order = Order::findOrFail($request->order_id);

            $result = $this->rajaOngkirService->store($request->all());

            if ($result) {
                // Update order with waybill data
                $order->update([
                    'tracking_number' => $result['waybill_number'] ?? null,
                    'waybill_data' => json_encode($result),
                    'status' => 'confirmed',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Waybill stored successfully',
                    'data' => $result,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to store waybill',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Waybill store error', [
                'message' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel waybill
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        try {
            if (!$order->tracking_number) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order does not have a waybill number',
                ], 400);
            }

            $result = $this->rajaOngkirService->cancel($order->tracking_number);

            if ($result) {
                // Update order status
                $order->update([
                    'status' => 'cancelled',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Waybill cancelled successfully',
                    'data' => $result,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel waybill',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Waybill cancel error', [
                'message' => $e->getMessage(),
                'order_id' => $order->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get waybill detail
     */
    public function detail(Request $request, Order $order): JsonResponse
    {
        try {
            if (!$order->tracking_number) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order does not have a waybill number',
                ], 400);
            }

            $result = $this->rajaOngkirService->detail($order->tracking_number);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'data' => $result,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to get waybill detail',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Waybill detail error', [
                'message' => $e->getMessage(),
                'order_id' => $order->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get waybill history
     */
    public function history(Request $request, Order $order): JsonResponse
    {
        try {
            if (!$order->tracking_number) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order does not have a waybill number',
                ], 400);
            }

            $result = $this->rajaOngkirService->historyAirwayBill($order->tracking_number);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'data' => $result,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to get waybill history',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Waybill history error', [
                'message' => $e->getMessage(),
                'order_id' => $order->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Request pickup
     */
    public function pickup(Request $request, Order $order): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pickup_date' => 'required|date|after:today',
            'pickup_time' => 'required|string',
            'pickup_address' => 'required|string',
            'pickup_contact' => 'required|string',
            'pickup_phone' => 'required|string',
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
            if (!$order->tracking_number) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order does not have a waybill number',
                ], 400);
            }

            $pickupData = array_merge($request->all(), [
                'waybill_number' => $order->tracking_number,
                'courier' => $order->courier_service,
                'weight' => $order->item_weight,
                'origin' => $order->origin_city,
                'destination' => $order->destination_city,
            ]);

            $result = $this->rajaOngkirService->pickup($pickupData);

            if ($result) {
                // Update order status to picked up
                $order->update([
                    'status' => 'picked_up',
                ]);

                // Create status history
                $order->statusHistory()->create([
                    'status' => 'picked_up',
                    'notes' => 'Pickup requested: ' . $request->pickup_date . ' ' . $request->pickup_time .
                               ($result['message'] ? ' - ' . $result['message'] : ''),
                    'updated_by' => auth()->id(),
                ]);

                // Store pickup data in order metadata if available
                if (isset($result['pickup_id'])) {
                    $order->update([
                        'metadata' => array_merge($order->metadata ?? [], [
                            'pickup_request' => $result
                        ])
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Pickup requested successfully' .
                                 ($result['message'] && $result['message'] !== 'Pickup request (sandbox mode)' ?
                                  ' - ' . $result['message'] : ''),
                    'data' => $result,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to request pickup',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Waybill pickup error', [
                'message' => $e->getMessage(),
                'order_id' => $order->id,
                'data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print label
     */
    public function printLabel(Request $request, Order $order): JsonResponse
    {
        try {
            if (!$order->tracking_number) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order does not have a waybill number',
                ], 400);
            }

            $result = $this->rajaOngkirService->printLabel($order->tracking_number);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Label generated successfully',
                    'data' => $result,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate label',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Waybill print label error', [
                'message' => $e->getMessage(),
                'order_id' => $order->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
