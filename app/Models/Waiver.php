<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStudio;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Waiver extends Model
{
    use HasFactory, BelongsToStudio;

    protected $fillable = [
        'studio_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class);
    }

    /**
     * Check if the waiver has been signed.
     */
    public function isSigned(): bool
    {
        return $this->signed_at !== null && $this->signature !== null;
    }
}
