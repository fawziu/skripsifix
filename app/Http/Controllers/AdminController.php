<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Complaint;
use App\Models\User;
use App\Models\OrderStatus;

class AdminController extends Controller
{
    /**
     * Show admin dashboard with real-time data
     */
    public function dashboard()
    {
        // Get real-time statistics
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $activeCouriers = User::whereHas('role', function($query) {
            $query->where('name', 'courier');
        })->where('is_active', true)->count();
        $openComplaints = Complaint::where('status', 'open')->count();

        // Get recent orders (last 5)
        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent complaints (last 5)
        $recentComplaints = Complaint::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get order statistics by status
        $orderStats = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Get complaint statistics by priority
        $complaintStats = Complaint::selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->get()
            ->pluck('count', 'priority')
            ->toArray();

        return view('admin.dashboard', compact(
            'totalOrders',
            'pendingOrders',
            'activeCouriers',
            'openComplaints',
            'recentOrders',
            'recentComplaints',
            'orderStats',
            'complaintStats'
        ));
    }

    /**
     * Get real-time dashboard data via AJAX
     */
    public function getDashboardData()
    {
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $activeCouriers = User::whereHas('role', function($query) {
            $query->where('name', 'courier');
        })->where('is_active', true)->count();
        $openComplaints = Complaint::where('status', 'open')->count();

        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentComplaints = Complaint::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'activeCouriers' => $activeCouriers,
            'openComplaints' => $openComplaints,
            'recentOrders' => $recentOrders,
            'recentComplaints' => $recentComplaints,
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'timezone' => 'WITA'
        ]);
    }
}
