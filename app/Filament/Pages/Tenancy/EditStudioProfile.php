<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Studio;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Tenancy\EditTenantProfile;

class EditStudioProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Studio settings';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['alpha_dash'])
                            ->helperText('Your studio URL identifier'),
                        TextInput::make('custom_domain')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Optional custom domain (e.g., booking.yourstudio.com)'),
                    ])
                    ->columns(2),

                Section::make('Branding')
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->directory('studio-logos')
                            ->maxSize(1024),
                        ColorPicker::make('primary_color')
                            ->label('Primary color'),
                        ColorPicker::make('secondary_color')
                            ->label('Secondary color'),
                    ])
                    ->columns(3),

                Section::make('Contact Information')
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Textarea::make('address')
                            ->rows(3),
                        Select::make('timezone')
                            ->options(collect(timezone_identifiers_list())
                                ->mapWithKeys(fn ($tz) => [$tz => $tz])
                                ->toArray())
                            ->searchable(),
                    ])
                    ->columns(2),

                Section::make('Website Content')
                    ->description('Customize the content shown on your public studio website.')
                    ->schema([
                        TextInput::make('settings.tagline')
                            ->label('Tagline')
                            ->placeholder('Your custom tattoo experience')
                            ->maxLength(100)
                            ->helperText('A short phrase shown in your website hero section'),
                        RichEditor::make('settings.about_text')
                            ->label('About Text')
                            ->placeholder('Tell visitors about your studio...')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'link',
                                'bulletList',
                                'orderedList',
                            ])
                            ->helperText('This text is displayed on your homepage about section'),
                        Textarea::make('settings.meta_description')
                            ->label('Meta Description (SEO)')
                            ->placeholder('A brief description of your studio for search engines...')
                            ->rows(2)
                            ->maxLength(160)
                            ->helperText('Shown in search engine results (max 160 characters)'),
                    ]),

                Section::make('Social Media')
                    ->description('Add your social media links to display on your public website.')
                    ->schema([
                        TextInput::make('settings.social_links.instagram')
                            ->label('Instagram URL')
                            ->url()
                            ->placeholder('https://instagram.com/yourstudio')
                            ->prefixIcon('heroicon-o-camera'),
                        TextInput::make('settings.social_links.facebook')
                            ->label('Facebook URL')
                            ->url()
                            ->placeholder('https://facebook.com/yourstudio')
                            ->prefixIcon('heroicon-o-user-group'),
                        TextInput::make('settings.social_links.tiktok')
                            ->label('TikTok URL')
                            ->url()
                            ->placeholder('https://tiktok.com/@yourstudio')
                            ->prefixIcon('heroicon-o-play'),
                        TextInput::make('settings.social_links.yelp')
                            ->label('Yelp URL')
                            ->url()
                            ->placeholder('https://yelp.com/biz/yourstudio')
                            ->prefixIcon('heroicon-o-star'),
                    ])
                    ->columns(2),

                Section::make('Business Hours')
                    ->description('Set your studio operating hours.')
                    ->schema([
                        Repeater::make('settings.business_hours')
                            ->label('')
                            ->schema([
                                Select::make('day')
                                    ->options([
                                        'Monday' => 'Monday',
                                        'Tuesday' => 'Tuesday',
                                        'Wednesday' => 'Wednesday',
                                        'Thursday' => 'Thursday',
                                        'Friday' => 'Friday',
                                        'Saturday' => 'Saturday',
                                        'Sunday' => 'Sunday',
                                    ])
                                    ->required()
                                    ->disabled(),
                                TimePicker::make('open')
                                    ->label('Open')
                                    ->seconds(false)
                                    ->required()
                                    ->disabled(fn (Get $get): bool => $get('is_closed')),
                                TimePicker::make('close')
                                    ->label('Close')
                                    ->seconds(false)
                                    ->required()
                                    ->disabled(fn (Get $get): bool => $get('is_closed')),
                                Toggle::make('is_closed')
                                    ->label('Closed')
                                    ->inline(false)
                                    ->live(),
                            ])
                            ->columns(4)
                            ->defaultItems(7)
                            ->reorderable(false)
                            ->addable(false)
                            ->deletable(false)
                            ->default(Studio::DEFAULT_SETTINGS['business_hours']),
                    ]),

                Section::make('Booking Settings')
                    ->description('Configure your online booking preferences.')
                    ->schema([
                        Toggle::make('settings.booking_enabled')
                            ->label('Enable Online Booking')
                            ->helperText('Allow clients to book appointments through your website')
                            ->live(),
                        TextInput::make('settings.booking_minimum_notice_hours')
                            ->label('Minimum Notice (Hours)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(720) // 30 days
                            ->default(24)
                            ->suffix('hours')
                            ->helperText('How far in advance clients must book')
                            ->visible(fn (Get $get): bool => $get('settings.booking_enabled')),
                        Select::make('settings.booking_deposit_type')
                            ->label('Deposit Type')
                            ->options([
                                'percentage' => 'Percentage of estimated cost',
                                'fixed' => 'Fixed amount',
                            ])
                            ->default('percentage')
                            ->visible(fn (Get $get): bool => $get('settings.booking_enabled')),
                        TextInput::make('settings.booking_deposit_amount')
                            ->label('Deposit Amount')
                            ->numeric()
                            ->minValue(0)
                            ->default(20)
                            ->suffix(fn (Get $get): string => $get('settings.booking_deposit_type') === 'percentage' ? '%' : '$')
                            ->helperText('Required deposit when booking')
                            ->visible(fn (Get $get): bool => $get('settings.booking_enabled')),
                        Textarea::make('settings.booking_instructions')
                            ->label('Booking Instructions')
                            ->placeholder('Special instructions or policies for clients when booking...')
                            ->rows(3)
                            ->helperText('Displayed to clients during the booking process')
                            ->visible(fn (Get $get): bool => $get('settings.booking_enabled')),
                    ])
                    ->columns(2),
            ]);
    }
}
