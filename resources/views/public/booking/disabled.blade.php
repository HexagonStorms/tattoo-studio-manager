<x-layouts.public>
    <x-slot:meta>
        <x-seo-meta
            title="Book an Appointment"
            :description="'Book your tattoo appointment at ' . $studio->name"
        />
    </x-slot:meta>

    {{-- Page Header --}}
    <section class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Book an Appointment</h1>
            <p class="text-xl text-gray-300 max-w-2xl mx-auto">
                Select an artist and schedule your next tattoo session
            </p>
        </div>
    </section>

    {{-- Booking Disabled Section --}}
    <section class="py-24 lg:py-32">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="bg-white rounded-2xl shadow-lg p-12">
                {{-- Icon --}}
                <div class="w-20 h-20 mx-auto mb-8 rounded-full flex items-center justify-center" style="background-color: var(--color-primary)">
                    <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>

                @if (isset($reason) && $reason === 'no_artists')
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">No Artists Available</h2>
                    <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                        We're currently not accepting online bookings as no artists are available. Please contact us directly to inquire about appointment availability.
                    </p>
                @else
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Online Booking Coming Soon</h2>
                    <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                        We're working on bringing you a seamless online booking experience. In the meantime, please contact us directly to schedule your appointment.
                    </p>
                @endif

                {{-- Contact Options --}}
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @if($studio->phone)
                        <a
                            href="tel:{{ $studio->phone }}"
                            class="inline-flex items-center justify-center px-6 py-3 text-base font-medium text-white rounded-md shadow-sm transition-colors hover:opacity-90"
                            style="background-color: var(--color-primary)"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            Call {{ $studio->phone }}
                        </a>
                    @endif

                    @if($studio->email)
                        <a
                            href="mailto:{{ $studio->email }}?subject=Appointment%20Inquiry"
                            class="inline-flex items-center justify-center px-6 py-3 text-base font-medium rounded-md border-2 transition-colors hover:bg-gray-50"
                            style="color: var(--color-primary); border-color: var(--color-primary)"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Email Us
                        </a>
                    @endif
                </div>

                {{-- Browse Artists --}}
                <div class="mt-8 pt-8 border-t">
                    <p class="text-sm text-gray-500 mb-4">Or browse our artists to find your perfect match:</p>
                    <a
                        href="{{ route('artists.index') }}"
                        class="inline-flex items-center text-sm font-medium hover:underline"
                        style="color: var(--color-primary)"
                    >
                        View Our Artists
                        <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- What to Expect --}}
    <section class="py-16 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">What to Expect</h2>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-full flex items-center justify-center text-white text-xl font-bold" style="background-color: var(--color-primary)">
                        1
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Consultation</h3>
                    <p class="text-gray-600 text-sm">
                        Discuss your ideas, placement, and sizing with your chosen artist.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-full flex items-center justify-center text-white text-xl font-bold" style="background-color: var(--color-primary)">
                        2
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Design</h3>
                    <p class="text-gray-600 text-sm">
                        Your artist will create a custom design based on your vision.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-full flex items-center justify-center text-white text-xl font-bold" style="background-color: var(--color-primary)">
                        3
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Session</h3>
                    <p class="text-gray-600 text-sm">
                        Come in for your appointment and leave with amazing new ink!
                    </p>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
