<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Complaint;
use App\Models\User;
use App\Models\OrderStatus;
use App\Models\CourierPricing;

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
        $recentOrders = Order::with('customer')
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

        $recentOrders = Order::with('customer')
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

    /**
     * Show courier bank info management page
     */
    public function courierBankInfo()
    {
        $courierPricings = CourierPricing::with('courier')->get();
        $couriers = User::whereHas('role', function($query) {
            $query->where('name', 'courier');
        })->get();

        return view('admin.courier-bank-info', compact('courierPricings', 'couriers'));
    }

    /**
     * Store new courier bank info
     */
    public function storeCourierBankInfo(Request $request)
    {
        $request->validate([
            'courier_id' => 'required|exists:users,id',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'account_holder' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            $pricing = CourierPricing::where('courier_id', $request->courier_id)->first();

            if (!$pricing) {
                // Create new pricing if doesn't exist
                $pricing = CourierPricing::create([
                    'courier_id' => $request->courier_id,
                    'base_fee' => 10000,
                    'per_kg_fee' => 5000,
                    'is_active' => true,
                ]);
            }

            $pricing->update([
                'bank_info' => [
                    'bank_name' => $request->bank_name,
                    'account_number' => $request->account_number,
                    'account_holder' => $request->account_holder,
                    'is_active' => $request->has('is_active'),
                ]
            ]);

            return redirect()->route('admin.courier-bank-info')
                ->with('success', 'Informasi bank kurir berhasil disimpan!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menyimpan informasi bank kurir. Silakan coba lagi.')
                ->withInput();
        }
    }

    /**
     * Show courier bank info for editing
     */
    public function showCourierBankInfo($id)
    {
        $pricing = CourierPricing::with('courier')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $pricing,
        ]);
    }

    /**
     * Update courier bank info
     */
    public function updateCourierBankInfo(Request $request, $id)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_holder' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            $pricing = CourierPricing::findOrFail($id);

            $pricing->update([
                'bank_info' => [
                    'bank_name' => $request->bank_name,
                    'account_number' => $request->account_number,
                    'account_holder' => $request->account_holder,
                    'is_active' => $request->has('is_active'),
                ]
            ]);

            return redirect()->route('admin.courier-bank-info')
                ->with('success', 'Informasi bank kurir berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui informasi bank kurir. Silakan coba lagi.')
                ->withInput();
        }
    }

    /**
     * Toggle courier bank info status
     */
    public function toggleCourierBankStatus($id)
    {
        try {
            $pricing = CourierPricing::findOrFail($id);
            $bankInfo = $pricing->bank_info ?? [];
            $bankInfo['is_active'] = !($bankInfo['is_active'] ?? false);

            $pricing->update(['bank_info' => $bankInfo]);

            return response()->json([
                'success' => true,
                'message' => 'Status informasi bank berhasil diubah',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status informasi bank',
            ], 500);
        }
    }
}
