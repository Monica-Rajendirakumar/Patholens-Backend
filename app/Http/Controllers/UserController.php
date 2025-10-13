<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // ✅ For specific user by ID
    public function getUser($id)
    {
        $user = User::select('id', 'name', 'email', 'age', 'phone_number')
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
                'phone_number' => $user->phone_number,
            ]
        ]);
    }
}
