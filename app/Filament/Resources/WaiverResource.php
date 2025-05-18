<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WaiverResource\Pages;
use App\Filament\Resources\WaiverResource\RelationManagers;
use App\Models\Waiver;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WaiverResource extends Resource
{
    protected static ?string $model = Waiver::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Client Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Client Information')
                    ->schema([
                        Forms\Components\TextInput::make('client_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('client_email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->required()
                            ->maxDate(now()->subYears(18)),
                        Forms\Components\Textarea::make('address')
                            ->required()
                            ->rows(3),
                        Forms\Components\TextInput::make('phone_number')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),
                
                Forms\Components\Section::make('Emergency Contact')
                    ->schema([
                        Forms\Components\TextInput::make('emergency_contact_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('emergency_contact_phone')
                            ->tel()
                            ->maxLength(255),
                    ])->columns(2),
                
                Forms\Components\Section::make('Medical Information')
                    ->schema([
                        Forms\Components\Textarea::make('medical_conditions')
                            ->rows(3),
                        Forms\Components\Toggle::make('has_allergies')
                            ->label('Has any allergies?')
                            ->reactive(),
                        Forms\Components\Textarea::make('allergies_description')
                            ->rows(3)
                            ->visible(fn (callable $get) => $get('has_allergies')),
                    ]),
                
                Forms\Components\Section::make('Tattoo Information')
                    ->schema([
                        Forms\Components\Textarea::make('tattoo_description')
                            ->label('Tattoo Description')
                            ->required()
                            ->rows(3),
                        Forms\Components\TextInput::make('tattoo_placement')
                            ->label('Tattoo Placement')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),
                
                Forms\Components\Section::make('Consent')
                    ->schema([
                        Forms\Components\Toggle::make('accepted_terms')
                            ->label('I have read and accept the terms and conditions')
                            ->required(),
                        Forms\Components\Toggle::make('accepted_aftercare')
                            ->label('I understand the aftercare instructions')
                            ->required(),
                        Forms\Components\DateTimePicker::make('signed_at')
                            ->label('Signed Date and Time'),
                        Forms\Components\TextInput::make('signature')
                            ->maxLength(255),
                    ]),
                
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Associated Staff Member')
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tattoo_placement')
                    ->searchable(),
                Tables\Columns\IconColumn::make('accepted_terms')
                    ->boolean(),
                Tables\Columns\IconColumn::make('accepted_aftercare')
                    ->boolean(),
                Tables\Columns\TextColumn::make('signed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Staff Member')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Staff Member'),
                Tables\Filters\Filter::make('signed')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('signed_at')),
                Tables\Filters\Filter::make('unsigned')
                    ->query(fn (Builder $query): Builder => $query->whereNull('signed_at')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListWaivers::route('/'),
            'create' => Pages\CreateWaiver::route('/create'),
            'edit' => Pages\EditWaiver::route('/{record}/edit'),
        ];
    }
}
