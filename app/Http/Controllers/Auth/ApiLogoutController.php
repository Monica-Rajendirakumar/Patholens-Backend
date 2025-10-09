<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class ApiLogoutController extends Controller
{
    /**
     * Logout user and revoke current access token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Delete the current access token
            $request->user()->currentAccessToken()->delete();

            // Log successful logout
            Log::info('User logged out successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ], 200);

        } catch (Throwable $e) {
            // Log error for debugging
            Log::error('Logout failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Logout failed. Please try again.',
                'errors' => config('app.debug') ? ['exception' => $e->getMessage()] : [],
            ], 500);
        }
    }

    /**
     * Logout from all devices (revoke all tokens)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logoutAll(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Delete all tokens for the user
            $request->user()->tokens()->delete();

            // Log successful logout from all devices
            Log::info('User logged out from all devices', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logged out from all devices successfully',
            ], 200);

        } catch (Throwable $e) {
            // Log error for debugging
            Log::error('Logout all devices failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Logout failed. Please try again.',
                'errors' => config('app.debug') ? ['exception' => $e->getMessage()] : [],
            ], 500);
        }
    }
}