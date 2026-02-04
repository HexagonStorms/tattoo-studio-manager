<x-layouts.public>
    <x-slot:meta>
        <title>Payment Successful - {{ $studio->name }}</title>
    </x-slot:meta>

    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-lg mx-auto px-4">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Success Header -->
                <div class="px-6 py-8 text-center" style="background-color: var(--color-primary)">
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-white/20 flex items-center justify-center">
                        <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white">Payment Successful!</h1>
                    <p class="text-white/80 mt-2">Your deposit has been received</p>
                </div>

                <div class="p-6">
                    <!-- Payment Summary -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Payment Details</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Amount Paid</dt>
                                <dd class="text-gray-900 font-medium">
                                    ${{ number_format($appointment->deposit_amount, 2) }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Payment Method</dt>
                                <dd class="text-gray-900 font-medium">
                                    {{ \App\Models\Appointment::paymentMethods()[$appointment->payment_method] ?? 'Card' }}
                                </dd>
                            </div>
                            @if($appointment->deposit_paid_at)
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Date</dt>
                                    <dd class="text-gray-900 font-medium">
                                        {{ $appointment->deposit_paid_at->format('M j, Y g:i A') }}
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Appointment Summary -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Appointment Details</h3>
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
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Duration</dt>
                                <dd class="text-gray-900 font-medium">
                                    {{ $appointment->formatted_duration }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- What's Next -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">What's Next?</h3>
                        <ul class="space-y-3 text-sm text-gray-600">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                A confirmation email has been sent to {{ $appointment->client_email }}
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-gray-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Arrive 10-15 minutes before your appointment
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-gray-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Bring a valid photo ID
                            </li>
                        </ul>
                    </div>

                    <!-- Actions -->
                    <div class="mt-8 space-y-3">
                        <a href="{{ route('home') }}"
                           class="block w-full text-center px-6 py-3 text-white font-semibold rounded-lg transition-all hover:opacity-90"
                           style="background-color: var(--color-primary)">
                            Return to Homepage
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="mt-6 text-center text-sm text-gray-500">
                <p>Questions? Contact us at</p>
                @if($studio->email)
                    <a href="mailto:{{ $studio->email }}" class="font-medium hover:underline" style="color: var(--color-primary)">
                        {{ $studio->email }}
                    </a>
                @endif
                @if($studio->phone)
                    <span class="mx-2">or</span>
                    <a href="tel:{{ $studio->phone }}" class="font-medium hover:underline" style="color: var(--color-primary)">
                        {{ $studio->phone }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-layouts.public>
