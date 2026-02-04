<x-layouts.public>
    <x-slot:meta>
        <title>Payment Cancelled - {{ $studio->name }}</title>
    </x-slot:meta>

    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-lg mx-auto px-4">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Cancel Header -->
                <div class="px-6 py-8 text-center bg-gray-800">
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-white/20 flex items-center justify-center">
                        <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white">Payment Cancelled</h1>
                    <p class="text-white/80 mt-2">Your payment was not processed</p>
                </div>

                <div class="p-6">
                    <!-- Message -->
                    <div class="text-center mb-6">
                        <p class="text-gray-600">
                            No worries! Your appointment is still saved. You can complete the deposit payment later or pay when you arrive at the studio.
                        </p>
                    </div>

                    <!-- Appointment Summary -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Your Appointment</h3>
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
                        </dl>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-3">
                        <form action="{{ route('booking.checkout', $appointment) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full px-6 py-3 text-white font-semibold rounded-lg transition-all hover:opacity-90"
                                    style="background-color: var(--color-primary)">
                                Try Payment Again
                            </button>
                        </form>

                        <a href="{{ route('booking.confirmation', $appointment) }}"
                           class="block w-full text-center px-6 py-3 text-gray-700 font-medium rounded-lg border-2 border-gray-300 hover:bg-gray-50 transition-all">
                            Pay Later at Studio
                        </a>

                        <a href="{{ route('home') }}"
                           class="block w-full text-center px-6 py-3 text-gray-500 font-medium hover:text-gray-700 transition-colors">
                            Return to Homepage
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="mt-6 text-center text-sm text-gray-500">
                <p>Need help? Contact us at</p>
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
