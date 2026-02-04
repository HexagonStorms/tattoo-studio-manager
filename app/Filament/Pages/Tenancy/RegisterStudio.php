<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Studio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Support\Str;

class RegisterStudio extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register studio';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $state, callable $set) {
                        $set('slug', Str::slug($state));
                    }),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(Studio::class, 'slug')
                    ->rules(['alpha_dash'])
                    ->helperText('This will be your studio URL: /admin/{slug}'),
            ]);
    }

    protected function handleRegistration(array $data): Studio
    {
        $studio = Studio::create($data);

        $studio->members()->attach(auth()->user(), ['role' => 'owner']);

        return $studio;
    }
}
