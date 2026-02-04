<x-layouts.public>
    <x-slot:meta>
        <x-seo-meta
            title="Booking Confirmed"
            :description="'Your appointment at ' . $studio->name . ' has been successfully booked.'"
        />
    </x-slot:meta>

    {{-- Page Header --}}
    <section class="bg-gray-900 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-2xl md:text-3xl font-bold">Booking Confirmation</h1>
        </div>
    </section>

    {{-- Confirmation Content --}}
    <section class="py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @livewire('public.booking-confirmation', ['appointment' => $appointment])
        </div>
    </section>
</x-layouts.public>
