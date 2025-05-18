<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waiver extends Model
{
    /** @use HasFactory<\Database\Factories\WaiverFactory> */
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'client_name',
        'client_email',
        'date_of_birth',
        'address',
        'phone_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'medical_conditions',
        'has_allergies',
        'allergies_description',
        'tattoo_description',
        'tattoo_placement',
        'accepted_terms',
        'accepted_aftercare',
        'signed_at',
        'signature',
    ];
    
    protected $casts = [
        'date_of_birth' => 'date',
        'has_allergies' => 'boolean',
        'accepted_terms' => 'boolean',
        'accepted_aftercare' => 'boolean',
        'signed_at' => 'datetime',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
