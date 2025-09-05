<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\CourierPricing;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
            Log::error('Error updating order status', [
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

    /**
     * Upload pickup proof photo
     */
    public function uploadPickupProof(Request $request, Order $order)
    {
        $user = Auth::user();

        // Check if courier is assigned to this order
        if ($order->courier_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengupdate pesanan ini'
            ], 403);
        }

        // Check if order status allows pickup proof upload
        if (!in_array($order->status, ['confirmed', 'assigned', 'picked_up'])) {
            return response()->json([
                'success' => false,
                'message' => 'Status pesanan tidak memungkinkan upload bukti pengambilan'
            ], 400);
        }

        $request->validate([
            'pickup_proof_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Handle file upload
            if ($request->hasFile('pickup_proof_photo')) {
                $file = $request->file('pickup_proof_photo');
                $filename = 'pickup_proof_' . $order->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('delivery_proofs', $filename, 'public');

                // Update order with pickup proof
                $order->update([
                    'pickup_proof_photo' => $path,
                    'pickup_proof_at' => now(),
                    'status' => 'picked_up'
                ]);

                // Update status history
                $order->statusHistory()->create([
                    'status' => 'picked_up',
                    'notes' => $request->notes ?? 'Bukti pengambilan paket berhasil diupload',
                    'updated_by' => $user->id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Bukti pengambilan paket berhasil diupload',
                    'data' => [
                        'order_id' => $order->id,
                        'pickup_proof_photo' => $path,
                        'pickup_proof_at' => $order->pickup_proof_at->format('d M Y H:i:s'),
                        'status' => $order->status
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Error uploading pickup proof', [
                'order_id' => $order->id,
                'courier_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload bukti pengambilan paket'
            ], 500);
        }
    }

    /**
     * Upload delivery proof photo
     */
    public function uploadDeliveryProof(Request $request, Order $order)
    {
        $user = Auth::user();

        // Check if courier is assigned to this order
        if ($order->courier_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengupdate pesanan ini'
            ], 403);
        }

        // Check if order status allows delivery proof upload
        if (!in_array($order->status, ['assigned', 'picked_up', 'in_transit'])) {
            return response()->json([
                'success' => false,
                'message' => 'Status pesanan tidak memungkinkan upload bukti pengiriman'
            ], 400);
        }

        $request->validate([
            'delivery_proof_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Handle file upload
            if ($request->hasFile('delivery_proof_photo')) {
                $file = $request->file('delivery_proof_photo');
                $filename = 'delivery_proof_' . $order->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('delivery_proofs', $filename, 'public');

                // Update order with delivery proof
                $order->update([
                    'delivery_proof_photo' => $path,
                    'delivery_proof_at' => now(),
                    'status' => 'delivered'
                ]);

                // Update status history
                $order->statusHistory()->create([
                    'status' => 'delivered',
                    'notes' => $request->notes ?? 'Bukti pengiriman paket berhasil diupload',
                    'updated_by' => $user->id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Bukti pengiriman paket berhasil diupload',
                    'data' => [
                        'order_id' => $order->id,
                        'delivery_proof_photo' => $path,
                        'delivery_proof_at' => $order->delivery_proof_at->format('d M Y H:i:s'),
                        'status' => $order->status
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Error uploading delivery proof', [
                'order_id' => $order->id,
                'courier_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload bukti pengiriman paket'
            ], 500);
        }
    }

    /**
     * Show courier pricing management page
     */
    public function pricing()
    {
        $user = Auth::user();

        // Get or create pricing for this courier
        $pricing = CourierPricing::where('courier_id', $user->id)->first();

        return view('courier.courier-pricing', compact('pricing'));
    }

    /**
     * Store courier pricing
     */
    public function storePricing(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'base_fee' => 'required|numeric|min:0',
            'per_kg_fee' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        try {
            // Check if pricing already exists
            $existingPricing = CourierPricing::where('courier_id', $user->id)->first();

            if ($existingPricing) {
                return redirect()->back()->with('error', 'Harga pengiriman sudah ada. Gunakan fitur update untuk mengubah harga.');
            }

            // Create new pricing
            $pricing = CourierPricing::create([
                'courier_id' => $user->id,
                'base_fee' => $request->base_fee,
                'per_kg_fee' => $request->per_kg_fee,
                'is_active' => $request->has('is_active') ? true : false
            ]);

            Log::info('Courier pricing created', [
                'courier_id' => $user->id,
                'pricing_id' => $pricing->id,
                'base_fee' => $pricing->base_fee,
                'per_kg_fee' => $pricing->per_kg_fee
            ]);

            return redirect()->back()->with('success', 'Harga pengiriman berhasil disimpan!');

        } catch (\Exception $e) {
            Log::error('Error creating courier pricing', [
                'courier_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Gagal menyimpan harga pengiriman. Silakan coba lagi.');
        }
    }

    /**
     * Update courier pricing
     */
    public function updatePricing(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'base_fee' => 'required|numeric|min:0',
            'per_kg_fee' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        try {
            $pricing = CourierPricing::where('courier_id', $user->id)->first();

            if (!$pricing) {
                return redirect()->back()->with('error', 'Harga pengiriman tidak ditemukan.');
            }

            // Update pricing
            $pricing->update([
                'base_fee' => $request->base_fee,
                'per_kg_fee' => $request->per_kg_fee,
                'is_active' => $request->has('is_active') ? true : false
            ]);

            Log::info('Courier pricing updated', [
                'courier_id' => $user->id,
                'pricing_id' => $pricing->id,
                'base_fee' => $pricing->base_fee,
                'per_kg_fee' => $pricing->per_kg_fee
            ]);

            return redirect()->back()->with('success', 'Harga pengiriman berhasil diupdate!');

        } catch (\Exception $e) {
            Log::error('Error updating courier pricing', [
                'courier_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Gagal mengupdate harga pengiriman. Silakan coba lagi.');
        }
    }

    /**
     * Get courier pricing data via API
     */
    public function getPricingData()
    {
        $user = Auth::user();

        try {
            $pricing = CourierPricing::where('courier_id', $user->id)->first();

            if (!$pricing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Harga pengiriman belum diatur'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $pricing->id,
                    'base_fee' => $pricing->base_fee,
                    'per_kg_fee' => $pricing->per_kg_fee,
                    'is_active' => $pricing->is_active,
                    'created_at' => $pricing->created_at->format('d M Y H:i'),
                    'updated_at' => $pricing->updated_at->format('d M Y H:i')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting courier pricing data', [
                'courier_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data harga pengiriman'
            ], 500);
        }
    }

    /**
     * Calculate delivery fee based on weight
     */
    public function calculateFee(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'weight' => 'required|numeric|min:0.1'
        ]);

        try {
            $pricing = CourierPricing::where('courier_id', $user->id)
                ->where('is_active', true)
                ->first();

            if (!$pricing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Harga pengiriman tidak ditemukan atau tidak aktif'
                ], 404);
            }

            $weight = $request->weight;
            $totalFee = $pricing->calculateFee($weight);

            return response()->json([
                'success' => true,
                'data' => [
                    'weight' => $weight,
                    'base_fee' => $pricing->base_fee,
                    'per_kg_fee' => $pricing->per_kg_fee,
                    'total_fee' => $totalFee,
                    'breakdown' => [
                        'base' => $pricing->base_fee,
                        'weight_cost' => $pricing->per_kg_fee * $weight,
                        'total' => $totalFee
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error calculating delivery fee', [
                'courier_id' => $user->id,
                'weight' => $request->weight,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung biaya pengiriman'
            ], 500);
        }
    }
}

