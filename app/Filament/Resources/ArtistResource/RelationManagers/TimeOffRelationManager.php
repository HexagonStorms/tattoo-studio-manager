<?php

namespace App\Filament\Resources\ArtistResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TimeOffRelationManager extends RelationManager
{
    protected static string $relationship = 'timeOffs';

    protected static ?string $title = 'Time Off';

    protected static ?string $icon = 'heroicon-o-calendar-days';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('is_all_day')
                    ->label('All Day')
                    ->default(true)
                    ->live()
                    ->columnSpanFull(),

                Forms\Components\DatePicker::make('start_date_only')
                    ->label('Start Date')
                    ->required()
                    ->native(false)
                    ->minDate(now()->subDays(1))
                    ->default(now())
                    ->visible(fn (Forms\Get $get): bool => $get('is_all_day'))
                    ->dehydrated(false),

                Forms\Components\DatePicker::make('end_date_only')
                    ->label('End Date')
                    ->required()
                    ->native(false)
                    ->minDate(now()->subDays(1))
                    ->default(now())
                    ->afterOrEqual('start_date_only')
                    ->visible(fn (Forms\Get $get): bool => $get('is_all_day'))
                    ->dehydrated(false),

                Forms\Components\DateTimePicker::make('start_date')
                    ->label('Start Date & Time')
                    ->required()
                    ->native(false)
                    ->minDate(now()->subDays(1))
                    ->default(now())
                    ->seconds(false)
                    ->visible(fn (Forms\Get $get): bool => ! $get('is_all_day')),

                Forms\Components\DateTimePicker::make('end_date')
                    ->label('End Date & Time')
                    ->required()
                    ->native(false)
                    ->minDate(now()->subDays(1))
                    ->afterOrEqual('start_date')
                    ->default(now()->addHours(4))
                    ->seconds(false)
                    ->visible(fn (Forms\Get $get): bool => ! $get('is_all_day')),

                Forms\Components\TextInput::make('reason')
                    ->label('Reason')
                    ->placeholder('e.g., Vacation, Sick leave, Personal day')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason')
            ->columns([
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start')
                    ->dateTime(fn ($record) => $record->is_all_day ? 'M j, Y' : 'M j, Y g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End')
                    ->dateTime(fn ($record) => $record->is_all_day ? 'M j, Y' : 'M j, Y g:i A')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_all_day')
                    ->boolean()
                    ->label('All Day'),
                Tables\Columns\TextColumn::make('reason')
                    ->placeholder('No reason specified')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record): string {
                        $now = now();
                        if ($record->end_date->isPast()) {
                            return 'past';
                        }
                        if ($record->start_date->isPast() && $record->end_date->isFuture()) {
                            return 'active';
                        }
                        return 'upcoming';
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'past' => 'Past',
                        'active' => 'Active Now',
                        'upcoming' => 'Upcoming',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'past' => 'gray',
                        'active' => 'danger',
                        'upcoming' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\Filter::make('upcoming')
                    ->label('Upcoming Only')
                    ->query(fn (Builder $query): Builder => $query->where('end_date', '>=', now()))
                    ->default(),
                Tables\Filters\TernaryFilter::make('is_all_day')
                    ->label('All Day'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Time Off')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Convert date-only fields to datetime for all-day entries
                        if ($data['is_all_day'] ?? false) {
                            if (isset($data['start_date_only'])) {
                                $data['start_date'] = $data['start_date_only'] . ' 00:00:00';
                            }
                            if (isset($data['end_date_only'])) {
                                $data['end_date'] = $data['end_date_only'] . ' 23:59:59';
                            }
                            unset($data['start_date_only'], $data['end_date_only']);
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(function (array $data): array {
                        // Set date-only fields for all-day entries in the edit form
                        if ($data['is_all_day'] ?? false) {
                            $data['start_date_only'] = \Carbon\Carbon::parse($data['start_date'])->toDateString();
                            $data['end_date_only'] = \Carbon\Carbon::parse($data['end_date'])->toDateString();
                        }
                        return $data;
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        if ($data['is_all_day'] ?? false) {
                            if (isset($data['start_date_only'])) {
                                $data['start_date'] = $data['start_date_only'] . ' 00:00:00';
                            }
                            if (isset($data['end_date_only'])) {
                                $data['end_date'] = $data['end_date_only'] . ' 23:59:59';
                            }
                            unset($data['start_date_only'], $data['end_date_only']);
                        }
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc')
            ->emptyStateHeading('No time off scheduled')
            ->emptyStateDescription('Add time off periods when this artist will be unavailable.')
            ->emptyStateIcon('heroicon-o-calendar-days');
    }
}
