<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'role' => 'required|string|in:customer,courier',
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
            $role = Role::where('name', $request->role)->first();
            
            if (!$role) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid role specified',
                    ], 400);
                }
                
                return redirect()->back()
                    ->withErrors(['role' => 'Invalid role specified'])
                    ->withInput();
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
                'role_id' => $role->id,
                'is_active' => true,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User registered successfully',
                    'data' => [
                        'user' => $user->load('role'),
                        'token' => $token,
                    ],
                ], 201);
            }

            // For web requests, redirect to login with success message
            return redirect()->route('login')
                ->with('success', 'Registration successful! Please login with your credentials.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration failed',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()
                ->withErrors(['general' => 'Registration failed. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
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
            if (!Auth::attempt($request->only('email', 'password'))) {
                if ($request->expectsJson()) {
                    throw ValidationException::withMessages([
                        'email' => ['The provided credentials are incorrect.'],
                    ]);
                }
                
                return redirect()->back()
                    ->withErrors(['email' => 'The provided credentials are incorrect.'])
                    ->withInput();
            }

            $user = User::where('email', $request->email)->first();
            
            if (!$user->isActive()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Account is deactivated',
                    ], 403);
                }
                
                Auth::logout();
                return redirect()->back()
                    ->withErrors(['email' => 'Account is deactivated.'])
                    ->withInput();
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'data' => [
                        'user' => $user->load('role'),
                        'token' => $token,
                    ],
                ]);
            }

            // For web requests, redirect to dashboard
            return redirect()->route('dashboard')
                ->with('success', 'Welcome back, ' . $user->name . '!');

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'errors' => $e->errors(),
                ], 401);
            }
            
            return redirect()->back()
                ->withErrors(['email' => 'Invalid credentials.'])
                ->withInput();
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login failed',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()
                ->withErrors(['general' => 'Login failed. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            // Always try to logout regardless of authentication state
            $user = $request->user();
            
            // If user exists and has Sanctum tokens, delete them
            if ($user && method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }
            
            // Logout from session (this will work even if user is not authenticated)
            Auth::logout();
            
            // Clear session
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Logged out successfully',
                ]);
            }

            // For web requests, redirect to login
            return redirect()->route('login')
                ->with('success', 'You have been logged out successfully.');

        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Logout error: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Logout failed',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            // Even if there's an error, try to redirect to login
            return redirect()->route('login')
                ->with('error', 'Logout completed, but there was an issue. Please login again.');
        }
    }

    /**
     * Get current user profile
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load('role');

            return response()->json([
                'success' => true,
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string',
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
            $user->update($request->only(['name', 'phone', 'address']));

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'data' => $user->load('role'),
                ]);
            }

            // For web requests, redirect back with success message
            return redirect()->back()
                ->with('success', 'Profile updated successfully!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile update failed',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()
                ->withErrors(['general' => 'Profile update failed. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
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

            if (!Hash::check($request->current_password, $user->password)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Current password is incorrect',
                    ], 400);
                }
                
                return redirect()->back()
                    ->withErrors(['current_password' => 'Current password is incorrect.'])
                    ->withInput();
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password updated successfully',
                ]);
            }

            // For web requests, redirect back with success message
            return redirect()->back()
                ->with('success', 'Password updated successfully!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password update failed',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()
                ->withErrors(['general' => 'Password update failed. Please try again.'])
                ->withInput();
        }
    }
}
