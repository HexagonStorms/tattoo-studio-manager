<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TodaysAppointmentsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = "Today's Schedule";

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Appointment::query()
                    ->whereDate('scheduled_at', today())
                    ->orderBy('scheduled_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Time')
                    ->time('g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Client')
                    ->searchable(),
                Tables\Columns\TextColumn::make('artist.display_name')
                    ->label('Artist'),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service')
                    ->placeholder('Custom'),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->formatStateUsing(fn (int $state): string => $this->formatDuration($state)),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Appointment::statuses()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        'no_show' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('deposit_paid_at')
                    ->label('Deposit')
                    ->boolean()
                    ->getStateUsing(fn (Appointment $record): bool => $record->deposit_paid_at !== null)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Appointment $record): string => AppointmentResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('No appointments scheduled for today')
            ->emptyStateDescription('When you have appointments, they will appear here.')
            ->emptyStateIcon('heroicon-o-calendar')
            ->paginated(false);
    }

    protected function formatDuration(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return "{$hours}h {$mins}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$mins}m";
        }
    }

    public static function canView(): bool
    {
        return Filament::getTenant() !== null;
    }
}
