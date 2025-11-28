<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    /**
     * Step 1: Send OTP to email for password reset
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'No account found with this email address.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->email;

        // Rate limiting: Max 3 OTP requests per email per hour
        $key = 'password-reset-otp:' . $email;
        
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Too many requests. Please try again in " . ceil($seconds / 60) . " minutes.",
            ], 429);
        }

        RateLimiter::hit($key, 3600); // 1 hour

        try {
            // Generate OTP
            $otp = PasswordResetOtp::generate($email);
            
            // Send email via Brevo SMTP
            Mail::to($email)->send(new OtpMail($otp));

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully to your email',
                'email' => $email,
                'expires_in_minutes' => 10
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Step 2: Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input',
                'errors' => $validator->errors()
            ], 422);
        }

        // Rate limiting: Max 5 verification attempts per email per 15 minutes
        $key = 'password-reset-verify:' . $request->email;
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many verification attempts. Please request a new OTP.',
            ], 429);
        }

        RateLimiter::hit($key, 900); // 15 minutes

        if (PasswordResetOtp::verify($request->email, $request->otp)) {
            RateLimiter::clear($key); // Clear attempts on success
            
            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully. You can now reset your password.',
                'email' => $request->email,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired OTP. Please try again.',
        ], 401);
    }

    /**
     * Step 3: Reset Password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if OTP was verified and not used
        if (!PasswordResetOtp::isVerifiedAndNotUsed($request->email)) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify OTP first or OTP has expired.',
            ], 401);
        }

        try {
            // Update user password
            $user = User::where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            // Mark OTP as used
            PasswordResetOtp::markAsUsed($request->email);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully. You can now login with your new password.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        return $this->sendOtp($request);
    }
}