<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CustomerController extends Controller
{
    /**
     * Show customer dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Get total orders count
        $totalOrders = Order::where('customer_id', $user->id)->count();

        // Get active orders count
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

        // Get active orders list
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
}

