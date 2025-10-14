<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Complaint;
use App\Services\OrderService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CustomerController extends Controller
{
    private OrderService $orderService;
    private WhatsAppNotificationService $whatsappService;

    public function __construct(OrderService $orderService, WhatsAppNotificationService $whatsappService)
    {
        $this->orderService = $orderService;
        $this->whatsappService = $whatsappService;
    }
    /**
     * Show customer dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Get total orders count
        $totalOrders = Order::where('customer_id', $user->id)->count();

        // Get active orders count (including awaiting_confirmation)
        $activeOrders = Order::where('customer_id', $user->id)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->count();

        // Get completed orders count
        $completedOrders = Order::where('customer_id', $user->id)
            ->where('status', 'delivered')
            ->count();

        // Get total spent
        $totalSpent = Order::where('customer_id', $user->id)
            ->where('status', 'delivered')
            ->sum('total_amount');

        // Get active orders list (including awaiting_confirmation)
        $activeOrdersList = Order::where('customer_id', $user->id)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->with('courier')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get recent completed orders
        $recentCompleted = Order::where('customer_id', $user->id)
            ->where('status', 'delivered')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent complaints
        $recentComplaints = Complaint::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('customer.dashboard', compact(
            'totalOrders',
            'activeOrders',
            'completedOrders',
            'totalSpent',
            'activeOrdersList',
            'recentCompleted',
            'recentComplaints'
        ));
    }

    /**
     * Get real-time dashboard data for AJAX
     */
    public function getDashboardData()
    {
        $user = Auth::user();

        // Get real-time statistics
        $totalOrders = Order::where('customer_id', $user->id)->count();

        $activeOrders = Order::where('customer_id', $user->id)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->count();

        $completedOrders = Order::where('customer_id', $user->id)
            ->where('status', 'delivered')
            ->count();

        $totalSpent = Order::where('customer_id', $user->id)
            ->where('status', 'delivered')
            ->sum('total_amount');

        // Get active orders list
        $activeOrdersList = Order::where('customer_id', $user->id)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->with('courier')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'status' => $order->status,
                    'item_description' => $order->item_description,
                    'destination_address' => $order->destination_address,
                    'total_cost' => $order->total_amount,
                    'courier_name' => $order->courier ? $order->courier->name : null,
                    'created_at' => $order->created_at->format('d M Y H:i')
                ];
            });

        // Get recent completed orders
        $recentCompleted = Order::where('customer_id', $user->id)
            ->where('status', 'delivered')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'item_description' => $order->item_description,
                    'total_cost' => $order->total_amount,
                    'completed_at' => $order->updated_at->format('d M Y')
                ];
            });

        // Get recent complaints
        $recentComplaints = Complaint::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($complaint) {
                return [
                    'id' => $complaint->id,
                    'title' => $complaint->title,
                    'description' => $complaint->description,
                    'status' => $complaint->status,
                    'created_at' => $complaint->created_at->format('d M Y')
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'totalOrders' => $totalOrders,
                'activeOrders' => $activeOrders,
                'completedOrders' => $completedOrders,
                'totalSpent' => $totalSpent,
                'activeOrdersList' => $activeOrdersList,
                'recentCompleted' => $recentCompleted,
                'recentComplaints' => $recentComplaints,
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'timezone' => 'WITA'
            ]
        ]);
    }

    /**
     * Confirm order delivery by customer
     */
    public function confirmDelivery(Request $request, Order $order): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user is the customer of this order
            if ($order->customer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengonfirmasi pesanan ini'
                ], 403);
            }

            // Check if order is in allowed status for customer confirmation
            if (!in_array($order->status, ['in_transit', 'awaiting_confirmation'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan belum dapat dikonfirmasi. Status saat ini: ' . $order->status
                ], 400);
            }

            // Handle optional receipt proof photo upload
            if ($request->hasFile('delivery_proof_photo')) {
                $request->validate([
                    'delivery_proof_photo' => 'image|mimes:jpg,jpeg,png|max:2048',
                    'notes' => 'nullable|string|max:500',
                ]);

                $photo = $request->file('delivery_proof_photo');
                $path = $photo->store('orders/' . $order->id . '/delivery_proof', 'public');

                $order->delivery_proof_photo = $path;
                $order->delivery_proof_at = now();
            }

            // Update order status to delivered
            $order->update([
                'status' => 'delivered',
                'delivered_at' => now(),
                'delivered_by' => $user->id
            ]);

            // Create status history
            $order->statusHistory()->create([
                'status' => 'delivered',
                'notes' => $request->get('notes') ? ('Customer: ' . $request->get('notes')) : 'Customer mengonfirmasi barang telah diterima dengan baik',
                'updated_by' => $user->id
            ]);

            // Generate WhatsApp notification link for courier
            $whatsappLink = $this->whatsappService->generateDeliveryConfirmationLink($order, $user);

            return response()->json([
                'success' => true,
                'message' => 'Terima kasih! Pesanan telah dikonfirmasi sebagai diterima',
                'data' => [
                    'order_id' => $order->id,
                    'status' => 'delivered',
                    'delivered_at' => $order->delivered_at->format('d M Y H:i:s')
                ],
                'whatsapp_link' => $whatsappLink
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengonfirmasi penerimaan pesanan: ' . $e->getMessage()
            ], 500);
        }
    }
}

