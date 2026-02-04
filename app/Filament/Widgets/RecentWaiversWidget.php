<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\WaiverResource;
use App\Models\Waiver;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentWaiversWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Recent Waivers';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Waiver::query()
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Client')
                    ->searchable(false)
                    ->sortable(false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date('M j, Y')
                    ->sortable(false),
                Tables\Columns\IconColumn::make('signed_at')
                    ->label('Signed')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->getStateUsing(fn (Waiver $record): bool => $record->signed_at !== null),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Waiver $record): string => WaiverResource::getUrl('edit', [
                        'record' => $record,
                        'tenant' => Filament::getTenant()->slug,
                    ])),
            ])
            ->emptyStateHeading('No waivers yet')
            ->emptyStateDescription('Waivers will appear here once clients complete them.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->paginated(false)
            ->headerActions([
                Tables\Actions\Action::make('viewAll')
                    ->label('View All')
                    ->url(WaiverResource::getUrl('index', [
                        'tenant' => Filament::getTenant()?->slug,
                    ]))
                    ->icon('heroicon-m-arrow-right')
                    ->iconPosition('after')
                    ->color('gray'),
            ]);
    }

    public static function canView(): bool
    {
        return Filament::getTenant() !== null;
    }
}
