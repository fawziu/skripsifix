<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ComplaintService
{
    /**
     * Create a new complaint
     */
    public function createComplaint(array $data, User $user): Complaint
    {
        DB::beginTransaction();

        try {
            $complaint = Complaint::create([
                'user_id' => $user->id,
                'order_id' => $data['order_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'],
                'type' => $data['type'],
                'priority' => $data['priority'] ?? 'medium',
                'status' => 'open',
            ]);

            DB::commit();

            return $complaint;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating complaint', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Assign complaint to admin
     */
    public function assignComplaint(Complaint $complaint, User $admin): bool
    {
        DB::beginTransaction();

        try {
            $complaint->update([
                'assigned_to' => $admin->id,
                'status' => 'in_progress',
            ]);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error assigning complaint', [
                'error' => $e->getMessage(),
                'complaint_id' => $complaint->id,
                'admin_id' => $admin->id,
            ]);
            return false;
        }
    }

    /**
     * Resolve complaint
     */
    public function resolveComplaint(Complaint $complaint, string $resolution, User $admin): bool
    {
        DB::beginTransaction();

        try {
            $complaint->markAsResolved($resolution);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error resolving complaint', [
                'error' => $e->getMessage(),
                'complaint_id' => $complaint->id,
            ]);
            return false;
        }
    }

    /**
     * Close complaint
     */
    public function closeComplaint(Complaint $complaint, User $admin): bool
    {
        DB::beginTransaction();

        try {
            $complaint->update([
                'status' => 'closed',
            ]);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error closing complaint', [
                'error' => $e->getMessage(),
                'complaint_id' => $complaint->id,
            ]);
            return false;
        }
    }

    /**
     * Get complaints by user role
     */
    public function getComplaintsByUser(User $user, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = match (true) {
            $user->isAdmin() => Complaint::with(['user', 'order', 'assignedTo']),
            $user->isCourier() => Complaint::whereHas('order', function($q) use ($user) {
                $q->where('courier_id', $user->id);
            })->with(['user', 'order', 'assignedTo']),
            default => Complaint::where('user_id', $user->id)->with(['order', 'assignedTo']),
        };

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
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
            case 'priority':
                $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")->orderBy('created_at', 'desc');
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
     * Get complaints assigned to admin
     */
    public function getAssignedComplaints(User $admin, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Complaint::where('assigned_to', $admin->id)
            ->with(['user', 'order']);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    /**
     * Get complaint statistics
     */
    public function getComplaintStatistics(): array
    {
        $total = Complaint::count();
        $open = Complaint::where('status', 'open')->count();
        $inProgress = Complaint::where('status', 'in_progress')->count();
        $resolved = Complaint::where('status', 'resolved')->count();
        $closed = Complaint::where('status', 'closed')->count();

        return [
            'total' => $total,
            'open' => $open,
            'in_progress' => $inProgress,
            'resolved' => $resolved,
            'closed' => $closed,
            'resolution_rate' => $total > 0 ? round((($resolved + $closed) / $total) * 100, 2) : 0,
        ];
    }
}
