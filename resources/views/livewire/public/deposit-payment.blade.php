<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-gray-200" style="background-color: var(--color-primary)">
            <h2 class="text-xl font-semibold text-white">
                @if($isPaid)
                    Deposit Paid
                @else
                    Secure Your Appointment
                @endif
            </h2>
        </div>

        <div class="p-6">
            @if($isPaid)
                <!-- Already Paid State -->
                <div class="text-center py-4">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Deposit Received</h3>
                    <p class="text-gray-600">
                        Your deposit of {{ $this->formattedAmount }} has been received.
                    </p>
                </div>
            @else
                <!-- Deposit Amount Display -->
                <div class="text-center mb-6">
                    <p class="text-sm text-gray-500 mb-1">Deposit Amount</p>
                    <p class="text-4xl font-bold" style="color: var(--color-primary)">
                        {{ $this->formattedAmount }}
                    </p>
                    <p class="text-sm text-gray-500 mt-2">
                        {{ $this->depositDescription }}
                    </p>
                </div>

                <!-- Appointment Summary -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Appointment Details</h4>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Date</dt>
                            <dd class="text-gray-900 font-medium">
                                {{ $appointment->scheduled_at->format('l, F j, Y') }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Time</dt>
                            <dd class="text-gray-900 font-medium">
                                {{ $appointment->scheduled_at->format('g:i A') }}
                            </dd>
                        </div>
                        @if($appointment->artist)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Artist</dt>
                                <dd class="text-gray-900 font-medium">
                                    {{ $appointment->artist->display_name }}
                                </dd>
                            </div>
                        @endif
                        @if($appointment->estimated_price)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Estimated Total</dt>
                                <dd class="text-gray-900 font-medium">
                                    ${{ number_format($appointment->estimated_price, 2) }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <!-- Error Message -->
                @if($errorMessage)
                    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm text-red-700">{{ $errorMessage }}</p>
                        </div>
                    </div>
                @endif

                <!-- Payment Actions -->
                <div class="space-y-3">
                    @if($this->stripeConfigured)
                        <button
                            wire:click="payWithCard"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-75 cursor-not-allowed"
                            class="w-full flex items-center justify-center px-6 py-3 text-white font-semibold rounded-lg transition-all hover:opacity-90 disabled:opacity-75"
                            style="background-color: var(--color-primary)"
                            {{ $isProcessing ? 'disabled' : '' }}
                        >
                            <span wire:loading.remove wire:target="payWithCard">
                                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                Pay with Card
                            </span>
                            <span wire:loading wire:target="payWithCard" class="flex items-center">
                                <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    @endif

                    <button
                        wire:click="payLater"
                        wire:loading.attr="disabled"
                        class="w-full flex items-center justify-center px-6 py-3 text-gray-700 font-medium rounded-lg border-2 border-gray-300 hover:bg-gray-50 transition-all"
                    >
                        Pay at Studio
                    </button>
                </div>

                <!-- Info Text -->
                <p class="mt-4 text-xs text-gray-500 text-center">
                    Your deposit secures your appointment and will be applied to your final total.
                    @if($this->stripeConfigured)
                        Payments are securely processed by Stripe.
                    @endif
                </p>
            @endif
        </div>
    </div>
</div>
