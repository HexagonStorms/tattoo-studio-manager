<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistTimeOff extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_id',
        'start_date',
        'end_date',
        'reason',
        'is_all_day',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_all_day' => 'boolean',
    ];

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    /**
     * Check if a given datetime falls within this time off period.
     */
    public function coversDatetime(Carbon $datetime): bool
    {
        if ($this->is_all_day) {
            // For all-day time off, compare dates only
            return $datetime->between(
                $this->start_date->startOfDay(),
                $this->end_date->endOfDay()
            );
        }

        return $datetime->between($this->start_date, $this->end_date);
    }

    /**
     * Check if any part of a time range overlaps with this time off.
     */
    public function overlapsRange(Carbon $start, Carbon $end): bool
    {
        $timeOffStart = $this->is_all_day ? $this->start_date->startOfDay() : $this->start_date;
        $timeOffEnd = $this->is_all_day ? $this->end_date->endOfDay() : $this->end_date;

        return $start->lt($timeOffEnd) && $end->gt($timeOffStart);
    }

    /**
     * Scope to get time off that overlaps with a date range.
     */
    public function scopeOverlapping($query, Carbon $start, Carbon $end)
    {
        return $query->where(function ($q) use ($start, $end) {
            $q->where('start_date', '<=', $end)
              ->where('end_date', '>=', $start);
        });
    }

    /**
     * Scope to get upcoming time off.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('end_date', '>=', now())
                     ->orderBy('start_date');
    }

    /**
     * Scope to get active time off (currently in effect).
     */
    public function scopeActive($query)
    {
        return $query->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }

    /**
     * Get a human-readable description of the time off period.
     */
    public function getFormattedPeriodAttribute(): string
    {
        $start = $this->start_date;
        $end = $this->end_date;

        if ($this->is_all_day) {
            if ($start->isSameDay($end)) {
                return $start->format('M j, Y');
            }
            return $start->format('M j') . ' - ' . $end->format('M j, Y');
        }

        if ($start->isSameDay($end)) {
            return $start->format('M j, Y g:i A') . ' - ' . $end->format('g:i A');
        }

        return $start->format('M j, g:i A') . ' - ' . $end->format('M j, Y g:i A');
    }
}
