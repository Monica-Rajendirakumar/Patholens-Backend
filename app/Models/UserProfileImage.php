<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class UserProfileImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_image',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'id',
        'user_id',
    ];

    /**
     * Get the user that owns the profile image
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full URL for the profile image
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->profile_image) {
            return null;
        }

        return Storage::disk('public')->url($this->profile_image);
    }
}