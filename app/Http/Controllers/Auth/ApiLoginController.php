<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ApiLoginController extends Controller
{
    /**
     * Login user and return authentication token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            // Validate input
            $validated = $request->validate([
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ], [
                'email.required' => 'Email is required.',
                'email.email' => 'Please enter a valid email address.',
                'password.required' => 'Password is required.',
            ]);

            // Find user by email
            $user = User::where('email', strtolower(trim($validated['email'])))->first();

            // Check if user exists and password is correct
            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password',
                ], 401);
            }

            // Revoke previous tokens (optional - for single device login)
            // $user->tokens()->delete();

            // Create new token
            $token = $user->createToken('api-token')->plainTextToken;

            // Log successful login (without sensitive data)
            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'age' => $user->age,
                        'gender' => $user->gender,
                        'phone_number' => $user->phone_number,
                        'created_at' => $user->created_at->toISOString(),
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 200);

        } catch (ValidationException $e) {
            // Return validation errors
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (Throwable $e) {
            // Log error for debugging
            Log::error('Login failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please try again.',
                'errors' => config('app.debug') ? ['exception' => $e->getMessage()] : [],
            ], 500);
        }
    }

    /**
     * Logout user (revoke token)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            // Log successful logout
            Log::info('User logged out successfully', [
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ], 200);

        } catch (Throwable $e) {
            // Log error for debugging
            Log::error('Logout failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Logout failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Get authenticated user profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'age' => $user->age,
                        'gender' => $user->gender,
                        'phone_number' => $user->phone_number,
                        'created_at' => $user->created_at->toISOString(),
                    ],
                ],
            ], 200);

        } catch (Throwable $e) {
            Log::error('Failed to fetch user profile', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user profile',
            ], 500);
        }
    }
}