<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Service Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Used in public URLs'),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Duration & Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Duration')
                            ->required()
                            ->numeric()
                            ->suffix('minutes')
                            ->minValue(15)
                            ->step(15)
                            ->default(60)
                            ->helperText('Typical duration for this service'),
                        Forms\Components\Select::make('price_type')
                            ->required()
                            ->options([
                                'fixed' => 'Fixed Price',
                                'hourly' => 'Hourly Rate',
                                'consultation' => 'Consultation Required',
                            ])
                            ->default('fixed')
                            ->live()
                            ->helperText('How this service is priced'),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->minValue(0)
                            ->visible(fn (Forms\Get $get) => in_array($get('price_type'), ['fixed', 'hourly']))
                            ->required(fn (Forms\Get $get) => in_array($get('price_type'), ['fixed', 'hourly']))
                            ->label(fn (Forms\Get $get) => $get('price_type') === 'hourly' ? 'Hourly Rate' : 'Price'),
                        Forms\Components\Toggle::make('deposit_required')
                            ->label('Deposit Required')
                            ->default(true)
                            ->helperText('Require a deposit when booking this service'),
                    ])->columns(2),

                Forms\Components\Section::make('Display Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive services are hidden from booking'),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->formatStateUsing(fn (int $state): string => self::formatDuration($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'fixed' => 'Fixed',
                        'hourly' => 'Hourly',
                        'consultation' => 'Consultation',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'fixed' => 'success',
                        'hourly' => 'info',
                        'consultation' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->placeholder('Contact for pricing')
                    ->sortable(),
                Tables\Columns\IconColumn::make('deposit_required')
                    ->boolean()
                    ->label('Deposit'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                Tables\Filters\SelectFilter::make('price_type')
                    ->options([
                        'fixed' => 'Fixed Price',
                        'hourly' => 'Hourly Rate',
                        'consultation' => 'Consultation',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (Service $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (Service $record): string => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Service $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (Service $record) => $record->update(['is_active' => ! $record->is_active])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }

    protected static function formatDuration(int $minutes): string
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
}
