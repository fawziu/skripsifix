<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get all users (admin only)
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user->isAdmin()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied. Admin privileges required.',
                    ], 403);
                }
                
                return redirect()->back()->with('error', 'Akses ditolak. Hanya admin yang dapat mengakses halaman ini.');
            }

            $filters = $request->only(['search', 'role', 'status', 'date_from', 'date_to', 'sort']);
            $role = $request->get('role');
            
            $users = $this->userService->getUsersByRole($role, $filters);
            $roles = $this->userService->getAvailableRoles();
            $statistics = $this->userService->getUserStatistics();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'users' => $users,
                        'roles' => $roles,
                        'statistics' => $statistics,
                    ],
                ]);
            }

            return view('users.index', compact('users', 'roles', 'statistics'));

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get users',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Gagal memuat data pengguna. Silakan coba lagi.');
        }
    }

    /**
     * Show user creation form
     */
    public function create(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user->isAdmin()) {
                return redirect()->back()->with('error', 'Akses ditolak. Hanya admin yang dapat mengakses halaman ini.');
            }

            $roles = $this->userService->getAvailableRoles();

            return view('users.create', compact('roles'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat halaman. Silakan coba lagi.');
        }
    }

    /**
     * Create a new user
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'role' => 'required|string|in:admin,courier,customer',
            'is_active' => 'sometimes|boolean',
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
            
            if (!$user->isAdmin()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied. Admin privileges required.',
                    ], 403);
                }
                
                return redirect()->back()->with('error', 'Akses ditolak. Hanya admin yang dapat membuat pengguna baru.');
            }

            $userData = $request->all();
            $userData['is_active'] = $request->boolean('is_active', true);

            $newUser = $this->userService->createUser($userData);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User created successfully',
                    'data' => $newUser,
                ], 201);
            }

            $roleName = ucfirst($request->role);
            return redirect()->route('users.index')
                ->with('success', "Pengguna {$roleName} berhasil dibuat!");

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create user',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal membuat pengguna. Silakan coba lagi.')
                ->withInput();
        }
    }

    /**
     * Show user details
     */
    public function show(Request $request, User $user)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->isAdmin()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied. Admin privileges required.',
                    ], 403);
                }
                
                return redirect()->back()->with('error', 'Akses ditolak. Hanya admin yang dapat melihat detail pengguna.');
            }

            $user->load(['role', 'customerOrders', 'courierOrders', 'complaints']);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $user,
                ]);
            }

            return view('users.show', compact('user'));

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get user details',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Gagal memuat detail pengguna. Silakan coba lagi.');
        }
    }

    /**
     * Show user edit form
     */
    public function edit(Request $request, User $user)
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->isAdmin()) {
                return redirect()->back()->with('error', 'Akses ditolak. Hanya admin yang dapat mengedit pengguna.');
            }

            $roles = $this->userService->getAvailableRoles();

            return view('users.edit', compact('user', 'roles'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memuat halaman edit. Silakan coba lagi.');
        }
    }

    /**
     * Update user information
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'role' => 'required|string|in:admin,courier,customer',
            'is_active' => 'sometimes|boolean',
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
            $currentUser = $request->user();
            
            if (!$currentUser->isAdmin()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied. Admin privileges required.',
                    ], 403);
                }
                
                return redirect()->back()->with('error', 'Akses ditolak. Hanya admin yang dapat mengedit pengguna.');
            }

            $userData = $request->all();
            $userData['is_active'] = $request->boolean('is_active', $user->is_active);

            $updatedUser = $this->userService->updateUser($user, $userData);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User updated successfully',
                    'data' => $updatedUser,
                ]);
            }

            return redirect()->route('users.index')
                ->with('success', 'Data pengguna berhasil diperbarui!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update user',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Gagal memperbarui data pengguna. Silakan coba lagi.')
                ->withInput();
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $currentUser = $request->user();
            
            if (!$currentUser->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin privileges required.',
                ], 403);
            }

            $success = $this->userService->updatePassword($user, $request->password);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password updated successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update password',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update password',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin privileges required.',
                ], 403);
            }

            $success = $this->userService->toggleUserStatus($user);

            if ($success) {
                $status = $user->fresh()->is_active ? 'activated' : 'deactivated';
                return response()->json([
                    'success' => true,
                    'message' => "User {$status} successfully",
                    'data' => $user->fresh()->load('role'),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle user status',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle user status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete user
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        try {
            $currentUser = $request->user();
            
            if (!$currentUser->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin privileges required.',
                ], 403);
            }

            // Prevent admin from deleting themselves
            if ($currentUser->id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete your own account',
                ], 400);
            }

            $success = $this->userService->deleteUser($user);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'User deleted successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin privileges required.',
                ], 403);
            }

            $statistics = $this->userService->getUserStatistics();

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
