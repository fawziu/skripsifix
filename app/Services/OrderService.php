<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class OrderService
{
    private RajaOngkirService $rajaOngkirService;
    private WhatsAppNotificationService $whatsappService;
    private TelegramService $telegramService;
    private ?TelegramClientService $telegramClientService;

    public function __construct(
        RajaOngkirService $rajaOngkirService, 
        WhatsAppNotificationService $whatsappService, 
        TelegramService $telegramService
    ) {
        $this->rajaOngkirService = $rajaOngkirService;
        $this->whatsappService = $whatsappService;
        $this->telegramService = $telegramService;
        
        // Always try to inject TelegramClientService if available
        try {
            $this->telegramClientService = app(TelegramClientService::class);
        } catch (\Throwable $e) {
            $this->telegramClientService = null;
        }
    }

    /**
     * Create a new order
     */
    public function createOrder(array $data, User $customer): Order
    {
        DB::beginTransaction();

        try {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_id' => $customer->id,
                'courier_id' => $data['courier_id'] ?? null,
                'item_description' => $data['item_description'],
                'item_weight' => $data['item_weight'],
                'item_price' => $data['item_price'],
                'service_fee' => $data['service_fee'] ?? 0,
                'shipping_cost' => $data['shipping_cost'] ?? 0,
                'total_amount' => $data['total_cost'] ?? $this->calculateTotalAmount($data),
                'shipping_method' => $data['shipping_method'],
                'payment_method' => $data['payment_method'] ?? 'cod',
                'origin_address' => $data['origin_address'],
                'origin_latitude' => $data['origin_latitude'] ?? null,
                'origin_longitude' => $data['origin_longitude'] ?? null,
                'destination_address' => $data['destination_address'],
                'destination_latitude' => $data['destination_latitude'] ?? null,
                'destination_longitude' => $data['destination_longitude'] ?? null,
                'origin_city' => $data['origin_city'] ?? null,
                'destination_city' => $data['destination_city'] ?? null,
                'courier_service' => $data['courier_service'] ?? null,
                'status' => 'pending',
            ]);

            // Create initial status
            $this->createOrderStatus($order, 'pending', $customer->id, 'Order created');

            DB::commit();

            return $order;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating order', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Calculate shipping cost using RajaOngkir
     */
    public function calculateRajaOngkirCost(array $data): array
    {
        try {
            $costData = [
                'origin' => $data['origin'],
                'destination' => $data['destination'],
                'weight' => $data['weight'] * 1000, // Convert to grams
                'courier' => $data['courier'] ?? 'jne',
            ];

            $results = $this->rajaOngkirService->calculateShippingCost($costData);

            if (empty($results)) {
                return [
                    'success' => false,
                    'message' => 'Unable to calculate shipping cost',
                    'data' => [],
                ];
            }

            return [
                'success' => true,
                'message' => 'Shipping cost calculated successfully',
                'data' => $results,
            ];
        } catch (Exception $e) {
            Log::error('Error calculating RajaOngkir cost', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Error calculating shipping cost',
                'data' => [],
            ];
        }
    }

    /**
     * Confirm order by admin
     */
    public function confirmOrder(Order $order, User $admin, array $data = []): array
    {
        DB::beginTransaction();

        try {
            $updateData = [
                'admin_id' => $admin->id,
                'status' => 'confirmed',
            ];

            // If RajaOngkir method, update with tracking info
            if ($order->isRajaOngkirShipping() && !empty($data['tracking_number'])) {
                $updateData['tracking_number'] = $data['tracking_number'];
                $updateData['courier_service'] = $data['courier_service'];
                $updateData['rajaongkir_response'] = $data['rajaongkir_response'] ?? null;
                $updateData['estimated_delivery'] = $data['estimated_delivery'] ?? null;
            }

            $order->update($updateData);

            // Create status update
            $this->createOrderStatus($order, 'confirmed', $admin->id, 'Order confirmed by admin');

            // Generate WhatsApp notification link
            $whatsappLink = $this->whatsappService->generateOrderConfirmationLink($order, $admin);

            // Send Telegram notification via Client API (from personal account)
            // Wrap in try-catch to prevent Telegram errors from breaking the order update
            if ($this->telegramClientService && $this->telegramClientService->isConfigured()) {
                try {
                    $this->telegramClientService->sendOrderStatusUpdate($order, 'confirmed', $admin);
                } catch (\Throwable $telegramError) {
                    // Log error but don't fail the order update
                    Log::warning('Telegram notification failed during order confirmation', [
                        'order_id' => $order->id,
                        'error' => $telegramError->getMessage()
                    ]);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'whatsapp_link' => $whatsappLink
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error confirming order', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
            ]);
            return [
                'success' => false,
                'whatsapp_link' => null
            ];
        }
    }

    /**
     * Assign courier to order
     */
    public function assignCourier(Order $order, User $courier, User $admin): array
    {
        DB::beginTransaction();

        try {
            $order->update([
                'courier_id' => $courier->id,
                'status' => 'assigned',
            ]);

            // Create status update
            $this->createOrderStatus($order, 'assigned', $admin->id, "Assigned to courier: {$courier->name}");

            // Generate WhatsApp notification link
            $whatsappLink = $this->whatsappService->generateCourierAssignmentLink($order, $courier);

            // Send Telegram notification via Client API (from personal account)
            // Wrap in try-catch to prevent Telegram errors from breaking the order update
            if ($this->telegramClientService && $this->telegramClientService->isConfigured()) {
                try {
                    $this->telegramClientService->sendOrderStatusUpdate($order, 'assigned', $admin);
                } catch (\Throwable $telegramError) {
                    // Log error but don't fail the order update
                    Log::warning('Telegram notification failed during courier assignment', [
                        'order_id' => $order->id,
                        'error' => $telegramError->getMessage()
                    ]);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'whatsapp_link' => $whatsappLink
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error assigning courier', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'courier_id' => $courier->id,
            ]);
            return [
                'success' => false,
                'whatsapp_link' => null
            ];
        }
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Order $order, string $status, User $user, string $notes = null): array
    {
        DB::beginTransaction();

        try {
            $order->update(['status' => $status]);

            // Create status update
            $this->createOrderStatus($order, $status, $user->id, $notes);

            // If order is confirmed and uses RajaOngkir, generate tracking number
            if ($status === 'confirmed' && $order->isRajaOngkirShipping() && !$order->tracking_number) {
                $this->generateTrackingNumber($order);
            }

            // Generate WhatsApp notification link
            $whatsappLink = $this->whatsappService->generateNotificationLink($order, $status, $user);

            // Send Telegram notification via Client API only
            // Wrap in try-catch to prevent Telegram errors from breaking the order update
            if ($this->telegramClientService && $this->telegramClientService->isConfigured()) {
                // Remember output buffer level before Telegram call
                $obLevelBeforeTelegram = ob_get_level();
                
                try {
                    $result = $this->telegramClientService->sendOrderStatusUpdate($order, $status, $user);
                    
                    if ($result) {
                        Log::info('Telegram notification sent successfully via OrderService', [
                            'order_id' => $order->id,
                            'status' => $status
                        ]);
                    } else {
                        Log::warning('Telegram notification returned false', [
                            'order_id' => $order->id,
                            'status' => $status
                        ]);
                    }
                    
                    // Restore output buffer level (TelegramClientService manages its own buffers)
                    while (ob_get_level() > $obLevelBeforeTelegram) {
                        @ob_end_clean();
                    }
                } catch (\Throwable $telegramError) {
                    // Log error but don't fail the order update
                    Log::warning('Telegram notification failed during order status update', [
                        'order_id' => $order->id,
                        'error' => $telegramError->getMessage(),
                        'trace' => $telegramError->getTraceAsString()
                    ]);
                    
                    // Restore output buffer level even on error
                    while (ob_get_level() > $obLevelBeforeTelegram) {
                        @ob_end_clean();
                    }
                }
            } else {
                Log::info('Telegram Client Service not configured or not available', [
                    'order_id' => $order->id,
                    'is_configured' => $this->telegramClientService ? $this->telegramClientService->isConfigured() : false
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'whatsapp_link' => $whatsappLink
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating order status', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'status' => $status,
            ]);
            return [
                'success' => false,
                'whatsapp_link' => null
            ];
        }
    }

    /**
     * Generate tracking number for RajaOngkir orders
     */
    public function generateTrackingNumber(Order $order): bool
    {
        try {
            if (!$order->isRajaOngkirShipping()) {
                return false;
            }

            // Prepare order data for RajaOngkir
            $orderData = [
                'courier' => $order->courier_service ?? 'jne',
                'origin' => $order->origin_city,
                'destination' => $order->destination_city,
                'weight' => $order->item_weight,
                'item_description' => $order->item_description,
                'item_price' => $order->item_price,
            ];

            // Create shipping order in RajaOngkir
            $result = $this->rajaOngkirService->createShippingOrder($orderData);

            if ($result['success'] && isset($result['tracking_number'])) {
                $order->tracking_number = $result['tracking_number'];
                $order->estimated_delivery = $result['estimated_delivery'] ?? null;
                $order->save();

                Log::info('Tracking number generated successfully', [
                    'order_id' => $order->id,
                    'tracking_number' => $result['tracking_number'],
                ]);

                return true;
            }

            Log::error('Failed to generate tracking number', [
                'order_id' => $order->id,
                'result' => $result,
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('Error generating tracking number', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
            ]);
            return false;
        }
    }

    /**
     * Get shipping label for order
     */
    public function getShippingLabel(Order $order): array
    {
        if (!$order->isRajaOngkirShipping() || !$order->tracking_number) {
            return [
                'success' => false,
                'message' => 'This order does not support label generation',
            ];
        }

        try {
            $result = $this->rajaOngkirService->getShippingLabel(
                $order->tracking_number,
                $order->courier_service
            );

            return $result;
        } catch (Exception $e) {
            Log::error('Error getting shipping label', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
            ]);

            return [
                'success' => false,
                'message' => 'Error generating shipping label',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Track order using RajaOngkir
     */
    public function trackOrder(Order $order): array
    {
        if (!$order->isRajaOngkirShipping() || !$order->tracking_number) {
            return [
                'success' => false,
                'message' => 'This order does not support automatic tracking',
                'data' => null,
            ];
        }

        try {
            $trackingData = $this->rajaOngkirService->trackShipment(
                $order->tracking_number,
                $order->courier_service
            );

            if (empty($trackingData)) {
                return [
                    'success' => false,
                    'message' => 'Unable to track shipment',
                    'data' => null,
                ];
            }

            return [
                'success' => true,
                'message' => 'Tracking data retrieved successfully',
                'data' => $trackingData,
            ];
        } catch (Exception $e) {
            Log::error('Error tracking order', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
            ]);

            return [
                'success' => false,
                'message' => 'Error tracking shipment',
                'data' => null,
            ];
        }
    }

    /**
     * Get orders by user role
     */
    public function getOrdersByUser(User $user, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = match (true) {
            $user->isAdmin() => Order::with(['customer', 'courier', 'admin']),
            $user->isCourier() => Order::where('courier_id', $user->id)->with(['customer', 'admin']),
            $user->isCustomer() => Order::where('customer_id', $user->id)->with(['courier', 'admin']),
            default => Order::where('customer_id', $user->id)->with(['courier', 'admin']),
        };

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['shipping_method'])) {
            $query->where('shipping_method', $filters['shipping_method']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Apply sorting
        $sort = $filters['sort'] ?? 'latest';
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'status':
                $query->orderBy('status', 'asc')->orderBy('created_at', 'desc');
                break;
            default: // latest
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query->paginate(15);
    }

    /**
     * Calculate total amount
     */
    private function calculateTotalAmount(array $data): float
    {
        $itemPrice = $data['item_price'] ?? 0;
        $serviceFee = $data['service_fee'] ?? 0;
        $shippingCost = $data['shipping_cost'] ?? 0;

        return $itemPrice + $serviceFee + $shippingCost;
    }

    /**
     * Create order status record
     */
    private function createOrderStatus(Order $order, string $status, int $updatedBy, string $notes = null): OrderStatus
    {
        return OrderStatus::create([
            'order_id' => $order->id,
            'updated_by' => $updatedBy,
            'status' => $status,
            'notes' => $notes,
        ]);
    }
}
