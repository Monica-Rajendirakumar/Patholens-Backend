<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasswordResetOtp extends Model
{
    protected $fillable = ['email', 'otp', 'expires_at', 'is_verified', 'is_used'];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_verified' => 'boolean',
        'is_used' => 'boolean',
    ];

    /**
     * Generate and store OTP for password reset
     */
    public static function generate(string $email): string
    {
        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Delete old unused OTPs for this email
        self::where('email', $email)
            ->where('is_used', false)
            ->delete();
        
        // Create new OTP (valid for 10 minutes)
        self::create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);
        
        return $otp;
    }

    /**
     * Verify OTP
     */
    public static function verify(string $email, string $otp): bool
    {
        $record = self::where('email', $email)
            ->where('otp', $otp)
            ->where('is_verified', false)
            ->where('is_used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($record) {
            $record->update(['is_verified' => true]);
            return true;
        }

        return false;
    }

    /**
     * Check if OTP is verified and not used
     */
    public static function isVerifiedAndNotUsed(string $email): bool
    {
        return self::where('email', $email)
            ->where('is_verified', true)
            ->where('is_used', false)
            ->where('expires_at', '>', Carbon::now())
            ->exists();
    }

    /**
     * Mark OTP as used after password reset
     */
    public static function markAsUsed(string $email): bool
    {
        return self::where('email', $email)
            ->where('is_verified', true)
            ->where('is_used', false)
            ->update(['is_used' => true]) > 0;
    }
}
