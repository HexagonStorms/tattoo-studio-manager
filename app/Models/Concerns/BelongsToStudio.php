<?php

namespace App\Models\Concerns;

use App\Models\Studio;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToStudio
{
    public static function bootBelongsToStudio(): void
    {
        static::creating(function (Model $model) {
            if (! $model->studio_id && Filament::getTenant()) {
                $model->studio_id = Filament::getTenant()->id;
            }
        });

        static::addGlobalScope('studio', function (Builder $query) {
            $tenant = Filament::getTenant();
            if ($tenant) {
                $query->where('studio_id', $tenant->id);
            }
        });
    }

    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }
}
