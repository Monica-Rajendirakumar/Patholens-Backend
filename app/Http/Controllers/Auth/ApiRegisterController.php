<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ApiRegisterController extends Controller
{
    /**
     * Register a new user and return authentication token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        try {
            // Validate input
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255', 'min:2'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'age' => ['nullable', 'integer', 'min:13', 'max:120'],
                'gender' => ['nullable', 'string', 'in:male,female,other,prefer_not_to_say'],
                'phone_number' => ['nullable', 'string', 'min:10', 'max:20', 'unique:users,phone_number'],
            ], [
                'name.required' => 'Name is required.',
                'name.min' => 'Name must be at least 2 characters.',
                'email.required' => 'Email is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email is already registered.',
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 8 characters.',
                'password.confirmed' => 'Password confirmation does not match.',
                'age.integer' => 'Age must be a valid number.',
                'age.min' => 'You must be at least 13 years old to register.',
                'age.max' => 'Please enter a valid age.',
                'gender.in' => 'Please select a valid gender option.',
                'phone_number.min' => 'Phone number must be at least 10 characters.',
                'phone_number.unique' => 'This phone number is already registered.',
            ]);

            // Use database transaction for data integrity
            $user = DB::transaction(function () use ($validated) {
                return User::create([
                    'name' => trim($validated['name']),
                    'email' => strtolower(trim($validated['email'])),
                    'password' => Hash::make($validated['password']),
                    'age' => $validated['age'] ?? null,
                    'gender' => $validated['gender'] ?? null,
                    'phone_number' => $validated['phone_number'] ?? null,
                ]);
            });

            // Create token
            $token = $user->createToken('api-token')->plainTextToken;

            // Log successful registration (without sensitive data)
            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
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
            ], 201);

        } catch (ValidationException $e) {
            // Return validation errors
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (Throwable $e) {
            // Log error for debugging
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'errors' => config('app.debug') ? ['exception' => $e->getMessage()] : [],
            ], 500);
        }
    }
}