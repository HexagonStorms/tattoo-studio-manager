<?php

namespace App\Filament\Resources\ArtistResource\RelationManagers;

use App\Models\ArtistAvailability;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AvailabilityRelationManager extends RelationManager
{
    protected static string $relationship = 'availabilities';

    protected static ?string $title = 'Weekly Schedule';

    protected static ?string $icon = 'heroicon-o-clock';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('day_of_week')
                    ->required()
                    ->options(ArtistAvailability::dayNames())
                    ->native(false),
                Forms\Components\TimePicker::make('start_time')
                    ->required()
                    ->seconds(false)
                    ->minutesStep(15)
                    ->default('10:00'),
                Forms\Components\TimePicker::make('end_time')
                    ->required()
                    ->seconds(false)
                    ->minutesStep(15)
                    ->default('18:00')
                    ->after('start_time'),
                Forms\Components\Toggle::make('is_available')
                    ->label('Available')
                    ->default(true)
                    ->helperText('Toggle off to mark as unavailable (e.g., lunch break)'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('day_of_week')
            ->columns([
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('Day')
                    ->formatStateUsing(fn (int $state): string => ArtistAvailability::dayNames()[$state] ?? 'Unknown')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start')
                    ->time('g:i A'),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('End')
                    ->time('g:i A'),
                Tables\Columns\IconColumn::make('is_available')
                    ->boolean()
                    ->label('Available'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('day_of_week')
                    ->options(ArtistAvailability::dayNames())
                    ->label('Day'),
                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Availability'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Time Block'),
                Tables\Actions\Action::make('set_default_schedule')
                    ->label('Set Default Schedule')
                    ->icon('heroicon-o-calendar')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalDescription('This will create a standard Monday-Saturday 10am-6pm schedule. Existing availability entries will be removed.')
                    ->action(function () {
                        $artist = $this->getOwnerRecord();

                        // Remove existing availability
                        $artist->availabilities()->delete();

                        // Create default schedule (Monday-Saturday)
                        $defaultDays = [
                            ArtistAvailability::MONDAY,
                            ArtistAvailability::TUESDAY,
                            ArtistAvailability::WEDNESDAY,
                            ArtistAvailability::THURSDAY,
                            ArtistAvailability::FRIDAY,
                            ArtistAvailability::SATURDAY,
                        ];

                        foreach ($defaultDays as $day) {
                            $artist->availabilities()->create([
                                'day_of_week' => $day,
                                'start_time' => '10:00',
                                'end_time' => '18:00',
                                'is_available' => true,
                            ]);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('day_of_week')
            ->emptyStateHeading('No schedule set')
            ->emptyStateDescription('Add time blocks to define when this artist is available for appointments.')
            ->emptyStateIcon('heroicon-o-clock')
            ->emptyStateActions([
                Tables\Actions\Action::make('set_default')
                    ->label('Set Default Schedule')
                    ->icon('heroicon-o-calendar')
                    ->action(function () {
                        $artist = $this->getOwnerRecord();

                        $defaultDays = [
                            ArtistAvailability::MONDAY,
                            ArtistAvailability::TUESDAY,
                            ArtistAvailability::WEDNESDAY,
                            ArtistAvailability::THURSDAY,
                            ArtistAvailability::FRIDAY,
                            ArtistAvailability::SATURDAY,
                        ];

                        foreach ($defaultDays as $day) {
                            $artist->availabilities()->create([
                                'day_of_week' => $day,
                                'start_time' => '10:00',
                                'end_time' => '18:00',
                                'is_available' => true,
                            ]);
                        }
                    }),
            ]);
    }
}
