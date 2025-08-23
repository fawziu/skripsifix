<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class UserService
{
    /**
     * Get users by role with pagination
     */
    public function getUsersByRole(string $role = null, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = User::with('role');

        // Filter by role if specified
        if ($role) {
            $roleModel = Role::where('name', $role)->first();
            if ($roleModel) {
                $query->where('role_id', $roleModel->id);
            }
        }

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('is_active', $filters['status'] === 'active');
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
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'email':
                $query->orderBy('email', 'asc');
                break;
            default: // latest
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query->paginate(15);
    }

    /**
     * Create a new user (admin, courier, or customer)
     */
    public function createUser(array $data): User
    {
        DB::beginTransaction();

        try {
            // Validate role
            $role = Role::where('name', $data['role'])->first();
            if (!$role) {
                throw new Exception('Invalid role specified');
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'],
                'address' => $data['address'],
                'role_id' => $role->id,
                'is_active' => $data['is_active'] ?? true,
            ]);

            DB::commit();

            return $user->load('role');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating user', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Update user information
     */
    public function updateUser(User $user, array $data): User
    {
        DB::beginTransaction();

        try {
            $updateData = [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
            ];

            // Update role if specified
            if (!empty($data['role'])) {
                $role = Role::where('name', $data['role'])->first();
                if ($role) {
                    $updateData['role_id'] = $role->id;
                }
            }

            // Update status if specified
            if (isset($data['is_active'])) {
                $updateData['is_active'] = $data['is_active'];
            }

            $user->update($updateData);

            DB::commit();

            return $user->load('role');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating user', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(User $user, string $newPassword): bool
    {
        try {
            $user->update([
                'password' => Hash::make($newPassword),
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Error updating user password', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return false;
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleUserStatus(User $user): bool
    {
        try {
            $user->update([
                'is_active' => !$user->is_active,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Error toggling user status', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return false;
        }
    }

    /**
     * Delete user (soft delete if implemented)
     */
    public function deleteUser(User $user): bool
    {
        try {
            // Check if user has any related data
            if ($user->customerOrders()->exists() || 
                $user->courierOrders()->exists() || 
                $user->complaints()->exists()) {
                throw new Exception('Cannot delete user with existing orders or complaints');
            }

            $user->delete();
            return true;
        } catch (Exception $e) {
            Log::error('Error deleting user', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return false;
        }
    }

    /**
     * Get user statistics
     */
    public function getUserStatistics(): array
    {
        $total = User::count();
        $active = User::where('is_active', true)->count();
        $inactive = User::where('is_active', false)->count();

        // Get counts by role
        $adminCount = User::whereHas('role', function ($query) {
            $query->where('name', 'admin');
        })->count();

        $courierCount = User::whereHas('role', function ($query) {
            $query->where('name', 'courier');
        })->count();

        $customerCount = User::whereHas('role', function ($query) {
            $query->where('name', 'customer');
        })->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'by_role' => [
                'admin' => $adminCount,
                'courier' => $courierCount,
                'customer' => $customerCount,
            ],
        ];
    }

    /**
     * Get available roles
     */
    public function getAvailableRoles(): \Illuminate\Database\Eloquent\Collection
    {
        return Role::all();
    }
}
