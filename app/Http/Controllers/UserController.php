<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // ✅ For specific user by ID
    public function getUser($id)
    {
        $user = User::select('id', 'name', 'email', 'age', 'phone_number','gender')
                    ->where('id', $id)
                    ->first();

        if ($user) {
            return response()->json([
                'status' => true,
                'data' => $user
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }
    }

    // ✅ For authenticated user (if using Sanctum)
    public function getAuthenticatedUser(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'status' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'age' => $user->age,
                'gender'=>$user->gender,
                'phone_number' => $user->phone_number,
            ]
        ]);
    }

    // ✅ Update user information
    public function updateUser(Request $request, $id)
    {
        // Find the user
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'age' => 'sometimes|required|integer|min:1|max:150',
            'gender' => 'sometimes|required|string|in:male,female,other',
            'phone_number' => [
                'sometimes',
                'required',
                'string',
                'max:15',
                Rule::unique('users')->ignore($user->id)
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update only the fields that are present in the request
        $user->update($request->only(['name', 'email', 'age', 'gender', 'phone_number']));

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'age' => $user->age,
                'gender' => $user->gender,
                'phone_number' => $user->phone_number,
            ]
        ]);
    }

    // ✅ Update authenticated user's own information
    public function updateAuthenticatedUser(Request $request)
    {
        $user = $request->user();

        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'age' => 'sometimes|required|integer|min:1|max:150',
            'gender' => 'sometimes|required|string|in:male,female,other',
            'phone_number' => [
                'sometimes',
                'required',
                'string',
                'max:15',
                Rule::unique('users')->ignore($user->id)
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update user
        $user->update($request->only(['name', 'email', 'age', 'gender', 'phone_number']));

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'age' => $user->age,
                'gender' => $user->gender,
                'phone_number' => $user->phone_number,
            ]
        ]);
    }
}