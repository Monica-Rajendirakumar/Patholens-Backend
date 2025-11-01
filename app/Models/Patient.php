<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'patient_name',
        'age',
        'gender',
        'contact_number',
        'diagnosising_image',
        'result',
        'confidence',
    ];

    // optional: link the user relation
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
