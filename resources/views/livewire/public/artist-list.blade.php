<div>
    <!-- Page Header -->
    <section class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Our Artists</h1>
            <p class="text-xl text-gray-300 max-w-2xl mx-auto">
                Meet the talented team bringing your visions to life
            </p>
        </div>
    </section>

    <!-- Filter Section -->
    @if(count($allSpecialties) > 0)
        <section class="bg-white border-b py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-sm font-medium text-gray-700">Filter by style:</span>
                    <button wire:click="clearFilter"
                            class="px-4 py-2 text-sm font-medium rounded-full transition-colors {{ $selectedSpecialty === '' ? 'text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                            style="{{ $selectedSpecialty === '' ? 'background-color: var(--color-primary)' : '' }}">
                        All
                    </button>
                    @foreach($allSpecialties as $specialty)
                        <button wire:click="filterBySpecialty('{{ $specialty }}')"
                                class="px-4 py-2 text-sm font-medium rounded-full transition-colors {{ $selectedSpecialty === $specialty ? 'text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                                style="{{ $selectedSpecialty === $specialty ? 'background-color: var(--color-primary)' : '' }}">
                            {{ $specialty }}
                        </button>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Artists Grid -->
    <section class="py-12 md:py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($artists->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No artists found</h3>
                    <p class="text-gray-500">
                        @if($selectedSpecialty)
                            No artists specialize in "{{ $selectedSpecialty }}".
                            <button wire:click="clearFilter" class="text-blue-600 hover:underline">Clear filter</button>
                        @else
                            Check back soon for our talented team.
                        @endif
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($artists as $artist)
                        <a href="{{ route('artists.show', $artist->slug) }}"
                           class="group block bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-all duration-300">
                            <!-- Artist Image -->
                            <div class="aspect-square overflow-hidden bg-gray-100">
                                @if($artist->featuredImages->first())
                                    <img src="{{ $artist->featuredImages->first()->image_url }}"
                                         alt="{{ $artist->display_name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300">
                                        <svg class="w-20 h-20 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Artist Info -->
                            <div class="p-5">
                                <h3 class="text-lg font-semibold mb-2 group-hover:opacity-80 transition-opacity" style="color: var(--color-primary)">
                                    {{ $artist->display_name }}
                                </h3>

                                @if($artist->specialties)
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(array_slice($artist->specialties, 0, 3) as $specialty)
                                            <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
                                                {{ $specialty }}
                                            </span>
                                        @endforeach
                                        @if(count($artist->specialties) > 3)
                                            <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-500">
                                                +{{ count($artist->specialties) - 3 }} more
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                @if($artist->is_accepting_bookings)
                                    <div class="mt-3 flex items-center text-sm text-green-600">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                        Accepting bookings
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl font-bold mb-4" style="color: var(--color-primary)">
                Found an artist you love?
            </h2>
            <p class="text-gray-600 mb-6">
                Click on their profile to view their portfolio and book a session.
            </p>
            <a href="{{ route('booking') }}"
               class="inline-flex items-center justify-center px-6 py-3 text-base font-medium rounded-lg text-white transition-colors hover:opacity-90"
               style="background-color: var(--color-primary)">
                Book an Appointment
            </a>
        </div>
    </section>
</div>
