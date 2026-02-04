<x-layouts.public>
    <x-slot:meta>
        <x-seo-meta
            title="Book an Appointment"
            :description="'Book your tattoo appointment at ' . $studio->name . '. Select your artist, choose a time, and secure your spot.'"
        />
    </x-slot:meta>

    {{-- Page Header --}}
    <section class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl md:text-4xl font-bold mb-2">Book Your Appointment</h1>
            <p class="text-lg text-gray-300 max-w-2xl mx-auto">
                Select your artist, choose a time, and let's create something amazing together.
            </p>
        </div>
    </section>

    {{-- Booking Wizard --}}
    <section class="py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @livewire('public.booking-wizard', ['artistSlug' => $artistSlug ?? null])
        </div>
    </section>
</x-layouts.public>
