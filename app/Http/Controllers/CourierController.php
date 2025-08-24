<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CourierController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Show courier dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Get assigned orders count (including pending orders that might be assigned)
        $assignedOrders = Order::where('courier_id', $user->id)
            ->whereIn('status', ['pending', 'assigned', 'picked_up', 'in_transit', 'confirmed'])
            ->count();

        // Get in progress orders count
        $inProgressOrders = Order::where('courier_id', $user->id)
            ->whereIn('status', ['picked_up', 'in_transit'])
            ->count();

        // Get completed orders today
        $completedToday = Order::where('courier_id', $user->id)
            ->where('status', 'delivered')
            ->whereDate('updated_at', Carbon::today())
            ->count();

        // Get total delivered orders
        $totalDelivered = Order::where('courier_id', $user->id)
            ->where('status', 'delivered')
            ->count();

        // Get current deliveries
        $currentDeliveries = Order::where('courier_id', $user->id)
            ->whereIn('status', ['pending', 'assigned', 'picked_up', 'in_transit', 'confirmed'])
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get recent completed deliveries
        $recentCompleted = Order::where('courier_id', $user->id)
            ->where('status', 'delivered')
            ->with('customer')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return view('courier.dashboard', compact(
            'assignedOrders',
            'inProgressOrders',
            'completedToday',
            'totalDelivered',
            'currentDeliveries',
            'recentCompleted'
        ));
    }

    /**
     * Get real-time dashboard data for AJAX
     */
    public function getDashboardData()
    {
        $user = Auth::user();

        // Get real-time statistics
        $assignedOrders = Order::where('courier_id', $user->id)
            ->whereIn('status', ['pending', 'assigned', 'picked_up', 'in_transit', 'confirmed'])
            ->count();

        $inProgressOrders = Order::where('courier_id', $user->id)
            ->whereIn('status', ['picked_up', 'in_transit'])
            ->count();

        $completedToday = Order::where('courier_id', $user->id)
            ->where('status', 'delivered')
            ->whereDate('updated_at', Carbon::today())
            ->count();

        $totalDelivered = Order::where('courier_id', $user->id)
            ->where('status', 'delivered')
            ->count();

        // Get current deliveries
        $currentDeliveries = Order::where('courier_id', $user->id)
            ->whereIn('status', ['pending', 'assigned', 'picked_up', 'in_transit', 'confirmed'])
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer ? $order->customer->name : 'Unknown Customer',
                    'status' => $order->status,
                    'item_description' => $order->item_description,
                    'destination_address' => $order->destination_address,
                    'created_at' => $order->created_at->format('d M Y H:i'),
                    'updated_at' => $order->updated_at->format('d M Y H:i')
                ];
            });

        // Get recent completed deliveries
        $recentCompleted = Order::where('courier_id', $user->id)
            ->where('status', 'delivered')
            ->with('customer')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' => $order->customer ? $order->customer->name : 'Unknown Customer',
                    'item_description' => $order->item_description,
                    'total_cost' => $order->total_amount,
                    'completed_at' => $order->updated_at->format('d M Y H:i')
                ];
            });

        // Get recent complaints related to courier's orders
        $recentComplaints = \App\Models\Complaint::whereHas('order', function($query) use ($user) {
            $query->where('courier_id', $user->id);
        })
        ->with(['user', 'order'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get()
        ->map(function ($complaint) {
            return [
                'id' => $complaint->id,
                'title' => $complaint->title,
                'type' => $complaint->type,
                'status' => $complaint->status,
                'priority' => $complaint->priority,
                'customer_name' => $complaint->user ? $complaint->user->name : 'Unknown Customer',
                'order_id' => $complaint->order ? $complaint->order->id : null,
                'created_at' => $complaint->created_at->format('d M Y H:i')
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'assignedOrders' => $assignedOrders,
                'inProgressOrders' => $inProgressOrders,
                'completedToday' => $completedToday,
                'totalDelivered' => $totalDelivered,
                'currentDeliveries' => $currentDeliveries,
                'recentCompleted' => $recentCompleted,
                'recentComplaints' => $recentComplaints,
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'timezone' => 'WITA'
            ]
        ]);
    }

    /**
     * Update order status (for couriers)
     */
    public function updateOrderStatus(Request $request, Order $order)
    {
        $user = Auth::user();

        // Check if courier is assigned to this order
        if ($order->courier_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengupdate pesanan ini'
            ], 403);
        }

        // Check if order is in valid status for courier update
        $validStatuses = ['pending', 'assigned', 'picked_up', 'in_transit', 'confirmed'];
        if (!in_array($order->status, $validStatuses)) {
            return response()->json([
                'success' => false,
                'message' => 'Status pesanan tidak dapat diupdate saat ini'
            ], 400);
        }

        $request->validate([
            'status' => 'required|in:picked_up,in_transit,delivered,failed',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $oldStatus = $order->status;
            $newStatus = $request->status;
            $notes = $request->notes;

            // Use OrderService to update status with WhatsApp notification
            $result = $this->orderService->updateOrderStatus($order, $newStatus, $user, $notes);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status pesanan berhasil diupdate',
                    'data' => [
                        'order_id' => $order->id,
                        'status' => $newStatus,
                        'updated_at' => $order->updated_at->format('d M Y H:i:s')
                    ],
                    'whatsapp_link' => $result['whatsapp_link']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status pesanan'
            ], 500);

        } catch (\Exception $e) {
            \Log::error('Error updating order status', [
                'order_id' => $order->id,
                'courier_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status pesanan'
            ], 500);
        }
    }

    /**
     * Get order details for courier
     */
    public function getOrderDetails(Order $order)
    {
        $user = Auth::user();

        // Check if courier is assigned to this order
        if ($order->courier_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk melihat pesanan ini'
            ], 403);
        }

        // Check if order exists and has valid status
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'item_description' => $order->item_description,
                    'item_weight' => $order->item_weight,
                    'item_price' => $order->item_price,
                    'origin_address' => $order->origin_address,
                    'destination_address' => $order->destination_address,
                    'shipping_cost' => $order->shipping_cost,
                    'service_fee' => $order->service_fee,
                    'total_amount' => $order->total_amount,
                    'shipping_method' => $order->shipping_method,
                    'courier_service' => $order->courier_service,
                    'created_at' => $order->created_at->format('d M Y H:i'),
                    'updated_at' => $order->updated_at->format('d M Y H:i')
                ],
                'customer' => $order->customer ? [
                    'id' => $order->customer->id,
                    'name' => $order->customer->name,
                    'email' => $order->customer->email,
                    'phone' => $order->customer->phone
                ] : null,
                'status_history' => $order->statusHistory()
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($status) {
                        return [
                            'status' => $status->status,
                            'notes' => $status->notes,
                            'updated_by' => $status->updated_by ? User::find($status->updated_by)->name : 'System',
                            'created_at' => $status->created_at->format('d M Y H:i')
                        ];
                    })
            ]
        ]);
    }
}

