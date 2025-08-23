<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\User;
use App\Services\ComplaintService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ComplaintController extends Controller
{
    private ComplaintService $complaintService;

    public function __construct(ComplaintService $complaintService)
    {
        $this->complaintService = $complaintService;
    }

    /**
     * Get all complaints (admin) or user's complaints
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $filters = $request->only(['status', 'type', 'priority', 'date_from', 'date_to', 'sort']);
            
            $complaints = $this->complaintService->getComplaintsByUser($user, $filters);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $complaints,
                ]);
            }

            // For web requests, return view with complaints data
            return view('complaints.index', compact('complaints'));

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get complaints',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to load complaints. Please try again.');
        }
    }

    /**
     * Create a new complaint
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:delivery,service,payment,other',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'order_id' => 'sometimes|exists:orders,id',
        ]);

        if ($validator->fails()) {
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
            
            // If order_id is provided, check if user has access to that order
            if ($request->order_id) {
                $order = \App\Models\Order::find($request->order_id);
                if (!$user->isAdmin() && $order->customer_id !== $user->id && $order->courier_id !== $user->id) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Access denied to this order',
                        ], 403);
                    }
                    return redirect()->back()->with('error', 'Access denied to this order.');
                }
            }

            $complaint = $this->complaintService->createComplaint($request->all(), $user);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Complaint created successfully',
                    'data' => $complaint->load(['user', 'order', 'assignedTo']),
                ], 201);
            }

            return redirect()
                ->route('complaints.show', $complaint)
                ->with('success', 'Complaint created successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create complaint',
                    'error' => $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to create complaint. Please try again.');
        }
    }

    /**
     * Show complaint edit form
     */
    public function edit(Request $request, Complaint $complaint)
    {
        try {
            $user = $request->user();
            
            // Check if user has access to edit this complaint
            if (!$user->isAdmin() && $complaint->user_id !== $user->id) {
                return redirect()->back()->with('error', 'Access denied to edit this complaint.');
            }

            $complaint->load(['user', 'order']);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $complaint,
                ]);
            }

            // For web requests, return edit view with complaint data
            return view('complaints.edit', compact('complaint'));

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load complaint for editing',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to load complaint for editing. Please try again.');
        }
    }

    /**
     * Update complaint
     */
    public function update(Request $request, Complaint $complaint)
    {
        try {
            $user = $request->user();
            
            // Check if user has access to update this complaint
            if (!$user->isAdmin() && $complaint->user_id !== $user->id) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied to update this complaint',
                    ], 403);
                }
                
                return redirect()->back()->with('error', 'Access denied to update this complaint.');
            }

            // Validation rules
            $rules = [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|in:delivery,service,payment,other',
                'priority' => 'sometimes|in:low,medium,high,urgent',
                'order_id' => 'sometimes|nullable|exists:orders,id',
            ];

            // Admin-only fields
            if ($user->isAdmin()) {
                $rules['status'] = 'sometimes|in:open,in_progress,resolved,closed';
                $rules['assigned_to'] = 'sometimes|nullable|exists:users,id';
                $rules['resolution'] = 'sometimes|nullable|string';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors(),
                    ], 422);
                }
                
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Update complaint
            $updateData = $request->only(['title', 'description', 'type', 'priority', 'order_id']);
            
            if ($user->isAdmin()) {
                $adminFields = $request->only(['status', 'assigned_to', 'resolution']);
                
                // Set timestamps for admin actions
                if (isset($adminFields['status'])) {
                    if ($adminFields['status'] === 'resolved' && $complaint->status !== 'resolved') {
                        $adminFields['resolved_at'] = now();
                    }
                }
                
                $updateData = array_merge($updateData, $adminFields);
            }

            $complaint->update($updateData);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Complaint updated successfully',
                    'data' => $complaint->load(['user', 'order', 'assignedTo']),
                ]);
            }

            // For web requests, redirect with success message
            return redirect()->route('complaints.show', $complaint)->with('success', 'Keluhan berhasil diperbarui.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update complaint',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update complaint. Please try again.')->withInput();
        }
    }

    /**
     * Get complaint details
     */
    public function show(Request $request, Complaint $complaint)
    {
        try {
            $user = $request->user();
            
            // Check if user has access to this complaint
            if (!$user->isAdmin() && $complaint->user_id !== $user->id && $complaint->assigned_to !== $user->id) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied',
                    ], 403);
                }
                
                return redirect()->back()->with('error', 'Access denied to this complaint.');
            }

            $complaint->load(['user', 'order', 'assignedTo']);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $complaint,
                ]);
            }

            // For web requests, return view with complaint data
            return view('complaints.show', compact('complaint'));

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get complaint details',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to load complaint details. Please try again.');
        }
    }

    /**
     * Assign complaint to admin (admin only)
     */
    public function assignComplaint(Request $request, Complaint $complaint): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|exists:users,id',
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
                    'message' => 'Only admins can assign complaints',
                ], 403);
            }

            $admin = User::find($request->admin_id);
            
            if (!$admin->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected user is not an admin',
                ], 400);
            }

            $success = $this->complaintService->assignComplaint($complaint, $admin);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Complaint assigned successfully',
                    'data' => $complaint->load(['user', 'order', 'assignedTo']),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign complaint',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign complaint',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resolve complaint (admin only)
     */
    public function resolveComplaint(Request $request, Complaint $complaint): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'resolution' => 'required|string',
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
                    'message' => 'Only admins can resolve complaints',
                ], 403);
            }

            $success = $this->complaintService->resolveComplaint($complaint, $request->resolution, $user);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Complaint resolved successfully',
                    'data' => $complaint->load(['user', 'order', 'assignedTo']),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve complaint',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve complaint',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Close complaint (admin only)
     */
    public function closeComplaint(Request $request, Complaint $complaint): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can close complaints',
                ], 403);
            }

            $success = $this->complaintService->closeComplaint($complaint, $user);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Complaint closed successfully',
                    'data' => $complaint->load(['user', 'order', 'assignedTo']),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to close complaint',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to close complaint',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get complaints assigned to current admin
     */
    public function assignedComplaints(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can view assigned complaints',
                ], 403);
            }

            $filters = $request->only(['status', 'priority']);
            $complaints = $this->complaintService->getAssignedComplaints($user, $filters);

            return response()->json([
                'success' => true,
                'data' => $complaints,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get assigned complaints',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get complaint statistics (admin only)
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admins can view statistics',
                ], 403);
            }

            $statistics = $this->complaintService->getComplaintStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
