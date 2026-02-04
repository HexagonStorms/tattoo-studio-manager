<?php

namespace App\Filament\Resources\ArtistResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PortfolioImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'portfolioImages';

    protected static ?string $title = 'Portfolio';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image_path')
                    ->label('Image')
                    ->image()
                    ->required()
                    ->directory('portfolio')
                    ->imageEditor()
                    ->maxSize(5120)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('title')
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\Select::make('style')
                    ->options([
                        'Traditional' => 'Traditional',
                        'Japanese' => 'Japanese',
                        'Blackwork' => 'Blackwork',
                        'Realism' => 'Realism',
                        'Neo-Traditional' => 'Neo-Traditional',
                        'Watercolor' => 'Watercolor',
                        'Geometric' => 'Geometric',
                        'Dotwork' => 'Dotwork',
                        'Tribal' => 'Tribal',
                        'Script/Lettering' => 'Script/Lettering',
                        'Portrait' => 'Portrait',
                        'New School' => 'New School',
                        'Trash Polka' => 'Trash Polka',
                        'Minimalist' => 'Minimalist',
                        'Fine Line' => 'Fine Line',
                    ])
                    ->searchable(),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_featured')
                    ->label('Featured Image')
                    ->helperText('Featured images appear prominently on the artist profile'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->square()
                    ->size(60),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->placeholder('Untitled'),
                Tables\Columns\TextColumn::make('style')
                    ->badge()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
                Tables\Filters\SelectFilter::make('style')
                    ->options([
                        'Traditional' => 'Traditional',
                        'Japanese' => 'Japanese',
                        'Blackwork' => 'Blackwork',
                        'Realism' => 'Realism',
                        'Neo-Traditional' => 'Neo-Traditional',
                        'Watercolor' => 'Watercolor',
                        'Geometric' => 'Geometric',
                        'Dotwork' => 'Dotwork',
                        'Minimalist' => 'Minimalist',
                        'Fine Line' => 'Fine Line',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }
}
