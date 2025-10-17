<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\UserProfileImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProfileImageController extends Controller
{
    private const MAX_FILE_SIZE = 2048; // 2MB in KB
    private const STORAGE_DISK = 'public';
    private const STORAGE_PATH = 'profile_images';

    /**
     * Upload or update profile image
     */
    public function upload(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'profile_image' => 'required|image|mimes:jpeg,jpg,png,webp|max:' . self::MAX_FILE_SIZE,
            ]);

            $user = $request->user();
            
            // Delete existing image if present
            $this->deleteExistingImage($user->id);

            // Store new image
            $path = $request->file('profile_image')->store(self::STORAGE_PATH, self::STORAGE_DISK);
            
            // Generate full URL
            $url = $this->getFullUrl($path);

            // Update or create record
            $profile = UserProfileImage::updateOrCreate(
                ['user_id' => $user->id],
                ['profile_image' => $path]
            );

            return response()->json([
                'success' => true,
                'message' => 'Profile image uploaded successfully',
                'data' => [
                    'profile_image_url' => $url,
                    'updated_at' => $profile->updated_at,
                ],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Profile image upload failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile image',
            ], 500);
        }
    }

    /**
     * Get user's profile image
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $profile = $user->profileImage;

            if (!$profile || !$profile->profile_image) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'profile_image_url' => null,
                    ],
                ], 200);
            }

            // Generate full URL from stored path
            $url = $this->getFullUrl($profile->profile_image);

            return response()->json([
                'success' => true,
                'data' => [
                    'profile_image_url' => $url,
                    'updated_at' => $profile->updated_at,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve profile image: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile image',
            ], 500);
        }
    }

    /**
     * Delete user's profile image
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if ($this->deleteExistingImage($user->id)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profile image deleted successfully',
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'No profile image found',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Profile image deletion failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete profile image',
            ], 500);
        }
    }

    /**
     * Delete existing profile image from storage and database
     */
    private function deleteExistingImage(int $userId): bool
    {
        $profile = UserProfileImage::where('user_id', $userId)->first();

        if (!$profile || !$profile->profile_image) {
            return false;
        }

        // Delete from storage
        if (Storage::disk(self::STORAGE_DISK)->exists($profile->profile_image)) {
            Storage::disk(self::STORAGE_DISK)->delete($profile->profile_image);
        }

        // Delete database record
        $profile->delete();

        return true;
    }

    /**
     * Generate full URL for the image
     * This replaces localhost with the actual server URL
     */
    private function getFullUrl(string $path): string
    {
        // Get the storage URL
        $url = Storage::disk(self::STORAGE_DISK)->url($path);
        
        // Replace localhost with APP_URL from .env
        // This ensures the URL works on Android devices
        $appUrl = rtrim(config('app.url'), '/');
        $url = str_replace('http://localhost', $appUrl, $url);
        
        return $url;
    }
}