<div>
    <!-- Hero Section -->
    <section class="relative bg-gray-900 text-white">
        <div class="absolute inset-0 bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 opacity-95"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
            <div class="text-center">
                @if($studio->logo_path)
                    <img src="{{ Storage::url($studio->logo_path) }}" alt="{{ $studio->name }}" class="h-24 w-auto mx-auto mb-8">
                @endif
                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    {{ $studio->name }}
                </h1>
                <p class="text-xl md:text-2xl text-gray-300 mb-8 max-w-2xl mx-auto">
                    {{ $tagline }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('booking') }}"
                       class="inline-flex items-center justify-center px-8 py-3 text-lg font-semibold rounded-lg text-white transition-all hover:opacity-90 hover:scale-105"
                       style="background-color: var(--color-primary)">
                        Book Your Session
                    </a>
                    <a href="{{ route('artists.index') }}"
                       class="inline-flex items-center justify-center px-8 py-3 text-lg font-semibold rounded-lg border-2 border-white text-white hover:bg-white hover:text-gray-900 transition-all">
                        Meet Our Artists
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-16 md:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-6" style="color: var(--color-primary)">
                    About Our Studio
                </h2>
                <div class="text-lg text-gray-600 leading-relaxed prose prose-gray max-w-none">
                    {!! $aboutText !!}
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Artists Section -->
    @if($featuredArtists->isNotEmpty())
        <section class="py-16 md:py-24 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: var(--color-primary)">
                        Featured Artists
                    </h2>
                    <p class="text-lg text-gray-600">
                        Meet the talented artists behind our work
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($featuredArtists as $artist)
                        <a href="{{ route('artists.show', $artist->slug) }}"
                           class="group block bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-all duration-300">
                            <div class="aspect-square overflow-hidden bg-gray-100">
                                @if($artist->featuredImages->first())
                                    <img src="{{ $artist->featuredImages->first()->image_url }}"
                                         alt="{{ $artist->display_name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                        <svg class="w-16 h-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-semibold mb-2 group-hover:text-opacity-80 transition-colors" style="color: var(--color-primary)">
                                    {{ $artist->display_name }}
                                </h3>
                                @if($artist->specialties)
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(array_slice($artist->specialties, 0, 3) as $specialty)
                                            <span class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
                                                {{ $specialty }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="text-center mt-12">
                    <a href="{{ route('artists.index') }}"
                       class="inline-flex items-center text-lg font-medium hover:underline"
                       style="color: var(--color-primary)">
                        View All Artists
                        <svg class="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    @endif

    <!-- CTA Section -->
    <section class="py-16 md:py-24" style="background-color: var(--color-primary)">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
                Ready to Get Inked?
            </h2>
            <p class="text-xl text-white/80 mb-8 max-w-2xl mx-auto">
                Book your consultation today and let's create something amazing together.
            </p>
            <a href="{{ route('booking') }}"
               class="inline-flex items-center justify-center px-8 py-3 text-lg font-semibold rounded-lg bg-white hover:bg-gray-100 transition-colors"
               style="color: var(--color-primary)">
                Book Your Appointment
            </a>
        </div>
    </section>
</div>
