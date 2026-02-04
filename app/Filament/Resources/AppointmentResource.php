<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Artist;
use App\Models\Service;
use App\Models\Waiver;
use App\Services\PaymentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'client_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Appointment')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Client Information')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\TextInput::make('client_name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('client_email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('client_phone')
                                    ->tel()
                                    ->required()
                                    ->maxLength(255),
                            ])->columns(3),

                        Forms\Components\Tabs\Tab::make('Appointment Details')
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                Forms\Components\Select::make('artist_id')
                                    ->label('Artist')
                                    ->relationship('artist', 'display_name', fn (Builder $query) => $query->active())
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live(),
                                Forms\Components\Select::make('service_id')
                                    ->label('Service')
                                    ->relationship('service', 'name', fn (Builder $query) => $query->active())
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                        if ($state) {
                                            $service = Service::find($state);
                                            if ($service) {
                                                $set('duration_minutes', $service->duration_minutes);
                                                if ($service->price_type === 'fixed' && $service->price) {
                                                    $set('estimated_price', $service->price);
                                                }
                                            }
                                        }
                                    }),
                                Forms\Components\DatePicker::make('scheduled_date')
                                    ->label('Date')
                                    ->required()
                                    ->native(false)
                                    ->minDate(now()->subDays(1))
                                    ->default(now())
                                    ->live()
                                    ->dehydrated(false),
                                Forms\Components\TimePicker::make('scheduled_time')
                                    ->label('Time')
                                    ->required()
                                    ->seconds(false)
                                    ->minutesStep(15)
                                    ->default('10:00')
                                    ->live()
                                    ->dehydrated(false),
                                Forms\Components\Hidden::make('scheduled_at'),
                                Forms\Components\TextInput::make('duration_minutes')
                                    ->label('Duration')
                                    ->required()
                                    ->numeric()
                                    ->suffix('minutes')
                                    ->minValue(15)
                                    ->step(15)
                                    ->default(60),
                                Forms\Components\Select::make('status')
                                    ->required()
                                    ->options(Appointment::statuses())
                                    ->default(Appointment::STATUS_PENDING),
                            ])->columns(3),

                        Forms\Components\Tabs\Tab::make('Tattoo Details')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                Forms\Components\Textarea::make('tattoo_description')
                                    ->label('Description')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('tattoo_placement')
                                    ->label('Placement')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('estimated_price')
                                    ->label('Estimated Price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Deposit & Payment')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Forms\Components\TextInput::make('deposit_amount')
                                    ->label('Deposit Amount')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->disabled()
                                    ->helperText('Use "Mark Deposit Paid" action to record payments'),
                                Forms\Components\DateTimePicker::make('deposit_paid_at')
                                    ->label('Deposit Paid At')
                                    ->disabled(),
                                Forms\Components\Select::make('payment_method')
                                    ->options(Appointment::paymentMethods())
                                    ->disabled(),
                                Forms\Components\TextInput::make('payment_reference')
                                    ->label('Reference/Transaction ID')
                                    ->maxLength(255)
                                    ->disabled(),
                                Forms\Components\Placeholder::make('stripe_info')
                                    ->label('Stripe Session ID')
                                    ->content(fn (?Appointment $record): string => $record?->stripe_checkout_session_id ?? 'N/A')
                                    ->visible(fn (?Appointment $record): bool => $record?->payment_method === Appointment::PAYMENT_METHOD_STRIPE),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Notes')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\Textarea::make('notes')
                                    ->label('Client Notes')
                                    ->helperText('Visible to client in confirmations')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('artist_notes')
                                    ->label('Artist Notes (Private)')
                                    ->helperText('Only visible to staff')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Waiver')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->schema([
                                Forms\Components\Select::make('waiver_id')
                                    ->label('Linked Waiver')
                                    ->relationship('waiver', 'client_name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('client_name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('client_email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('phone_number')
                                            ->tel()
                                            ->maxLength(255),
                                    ])
                                    ->helperText('Link to an existing waiver or create a new one'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Date & Time')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('artist.display_name')
                    ->label('Artist')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service')
                    ->placeholder('Custom')
                    ->toggleable(),
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
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->formatStateUsing(fn (int $state): string => self::formatDuration($state))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(Appointment::statuses()),
                Tables\Filters\SelectFilter::make('artist_id')
                    ->relationship('artist', 'display_name')
                    ->label('Artist')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('scheduled_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scheduled_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scheduled_at', '<=', $date),
                            );
                    }),
                Tables\Filters\TernaryFilter::make('deposit_paid')
                    ->label('Deposit Status')
                    ->placeholder('All')
                    ->trueLabel('Paid')
                    ->falseLabel('Unpaid')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('deposit_paid_at'),
                        false: fn (Builder $query) => $query->whereNull('deposit_paid_at')
                            ->whereNotNull('deposit_amount')
                            ->where('deposit_amount', '>', 0),
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('confirm')
                        ->label('Confirm')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Appointment $record): bool => $record->status === Appointment::STATUS_PENDING)
                        ->action(function (Appointment $record) {
                            $record->confirm();
                            Notification::make()
                                ->title('Appointment confirmed')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('complete')
                        ->label('Mark Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('info')
                        ->requiresConfirmation()
                        ->visible(fn (Appointment $record): bool => in_array($record->status, [
                            Appointment::STATUS_PENDING,
                            Appointment::STATUS_CONFIRMED,
                        ]))
                        ->action(function (Appointment $record) {
                            $record->complete();
                            Notification::make()
                                ->title('Appointment marked as completed')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('no_show')
                        ->label('Mark No-Show')
                        ->icon('heroicon-o-user-minus')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->visible(fn (Appointment $record): bool => in_array($record->status, [
                            Appointment::STATUS_PENDING,
                            Appointment::STATUS_CONFIRMED,
                        ]))
                        ->action(function (Appointment $record) {
                            $record->markNoShow();
                            Notification::make()
                                ->title('Appointment marked as no-show')
                                ->warning()
                                ->send();
                        }),
                    Tables\Actions\Action::make('cancel')
                        ->label('Cancel')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('cancellation_reason')
                                ->label('Cancellation Reason')
                                ->rows(2),
                        ])
                        ->visible(fn (Appointment $record): bool => $record->canBeCancelled())
                        ->action(function (Appointment $record, array $data) {
                            $record->cancel($data['cancellation_reason'] ?? null);
                            Notification::make()
                                ->title('Appointment cancelled')
                                ->warning()
                                ->send();
                        }),

                    // Mark Deposit Paid Action
                    Tables\Actions\Action::make('markDepositPaid')
                        ->label('Mark Deposit Paid')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn (Appointment $record): bool => !$record->is_deposit_paid)
                        ->form([
                            Forms\Components\Select::make('payment_method')
                                ->label('Payment Method')
                                ->options(Appointment::manualPaymentMethods())
                                ->required(),
                            Forms\Components\TextInput::make('payment_reference')
                                ->label('Reference/Note')
                                ->placeholder('Transaction ID, receipt number, etc.')
                                ->maxLength(255),
                        ])
                        ->action(function (Appointment $record, array $data): void {
                            $paymentService = app(PaymentService::class);
                            $paymentService->markAsPaid(
                                $record,
                                $data['payment_method'],
                                $data['payment_reference'] ?? null
                            );

                            Notification::make()
                                ->success()
                                ->title('Deposit marked as paid')
                                ->body('Payment method: ' . Appointment::paymentMethods()[$data['payment_method']])
                                ->send();
                        }),

                    // Refund Deposit Action
                    Tables\Actions\Action::make('refundDeposit')
                        ->label('Refund Deposit')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('danger')
                        ->visible(fn (Appointment $record): bool => $record->canBeRefunded())
                        ->requiresConfirmation()
                        ->modalHeading('Refund Deposit')
                        ->modalDescription('This will refund the deposit via Stripe. This action cannot be undone.')
                        ->form([
                            Forms\Components\TextInput::make('reason')
                                ->label('Refund Reason')
                                ->placeholder('Reason for refund (optional)')
                                ->maxLength(255),
                        ])
                        ->action(function (Appointment $record, array $data): void {
                            $paymentService = app(PaymentService::class);
                            $success = $paymentService->refundDeposit($record, $data['reason'] ?? null);

                            if ($success) {
                                Notification::make()
                                    ->success()
                                    ->title('Deposit refunded')
                                    ->body('The deposit has been refunded via Stripe.')
                                    ->send();
                            } else {
                                Notification::make()
                                    ->danger()
                                    ->title('Refund failed')
                                    ->body('Unable to process the refund. Please check the logs or try again.')
                                    ->send();
                            }
                        }),
                    Tables\Actions\Action::make('send_reminder')
                        ->label('Send Reminder')
                        ->icon('heroicon-o-bell')
                        ->color('primary')
                        ->visible(fn (Appointment $record): bool => in_array($record->status, [
                            Appointment::STATUS_PENDING,
                            Appointment::STATUS_CONFIRMED,
                        ]) && $record->scheduled_at->isFuture())
                        ->requiresConfirmation()
                        ->modalDescription('Send a reminder email to the client about their upcoming appointment.')
                        ->action(function (Appointment $record) {
                            // TODO: Implement reminder email
                            Notification::make()
                                ->title('Reminder sent')
                                ->body('Reminder functionality coming soon!')
                                ->info()
                                ->send();
                        }),
                    Tables\Actions\Action::make('view_waiver')
                        ->label('View Waiver')
                        ->icon('heroicon-o-document-text')
                        ->color('gray')
                        ->visible(fn (Appointment $record): bool => $record->waiver_id !== null)
                        ->url(fn (Appointment $record): string => WaiverResource::getUrl('edit', ['record' => $record->waiver_id])),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Client Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('client_name'),
                        Infolists\Components\TextEntry::make('client_email')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('client_phone')
                            ->copyable(),
                    ])->columns(3),

                Infolists\Components\Section::make('Appointment Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('scheduled_at')
                            ->label('Date & Time')
                            ->dateTime('l, F j, Y \a\t g:i A'),
                        Infolists\Components\TextEntry::make('artist.display_name')
                            ->label('Artist'),
                        Infolists\Components\TextEntry::make('service.name')
                            ->label('Service')
                            ->placeholder('Custom'),
                        Infolists\Components\TextEntry::make('duration_minutes')
                            ->label('Duration')
                            ->formatStateUsing(fn (int $state): string => self::formatDuration($state)),
                        Infolists\Components\TextEntry::make('status')
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
                    ])->columns(3),

                Infolists\Components\Section::make('Tattoo Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('tattoo_description')
                            ->label('Description')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('tattoo_placement')
                            ->label('Placement'),
                        Infolists\Components\TextEntry::make('estimated_price')
                            ->label('Estimated Price')
                            ->money('USD'),
                    ])->columns(2)
                    ->visible(fn (Appointment $record): bool => $record->tattoo_description || $record->tattoo_placement),

                Infolists\Components\Section::make('Payment Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('deposit_amount')
                            ->label('Deposit Amount')
                            ->money('USD'),
                        Infolists\Components\TextEntry::make('deposit_paid_at')
                            ->label('Paid At')
                            ->dateTime()
                            ->placeholder('Not paid'),
                        Infolists\Components\TextEntry::make('payment_method')
                            ->label('Payment Method')
                            ->formatStateUsing(fn (?string $state): string => $state ? (Appointment::paymentMethods()[$state] ?? $state) : '-'),
                        Infolists\Components\TextEntry::make('payment_reference')
                            ->label('Reference')
                            ->placeholder('-'),
                    ])->columns(4)
                    ->visible(fn (Appointment $record): bool => $record->deposit_amount > 0),

                Infolists\Components\Section::make('Refund Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('refund_amount')
                            ->money('USD')
                            ->label('Refund Amount'),
                        Infolists\Components\TextEntry::make('refunded_at')
                            ->label('Refunded At')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('refund_reason')
                            ->label('Reason')
                            ->placeholder('No reason provided'),
                    ])->columns(3)
                    ->visible(fn (Appointment $record): bool => $record->is_refunded),

                Infolists\Components\Section::make('Notes')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Client Notes')
                            ->columnSpanFull()
                            ->placeholder('No client notes'),
                        Infolists\Components\TextEntry::make('artist_notes')
                            ->label('Artist Notes (Private)')
                            ->columnSpanFull()
                            ->placeholder('No artist notes'),
                    ])
                    ->visible(fn (Appointment $record): bool => $record->notes || $record->artist_notes),

                Infolists\Components\Section::make('Cancellation')
                    ->schema([
                        Infolists\Components\TextEntry::make('cancelled_at')
                            ->label('Cancelled At')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('cancellation_reason')
                            ->label('Reason')
                            ->placeholder('No reason provided'),
                    ])->columns(2)
                    ->visible(fn (Appointment $record): bool => $record->status === Appointment::STATUS_CANCELLED),
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
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'view' => Pages\ViewAppointment::route('/{record}'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
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
