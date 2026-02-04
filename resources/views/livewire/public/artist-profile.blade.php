<div>
    <!-- Artist Header -->
    <section class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-8">
                <!-- Artist Avatar -->
                <div class="w-48 h-48 rounded-full overflow-hidden bg-gray-700 flex-shrink-0 ring-4 ring-white/20">
                    @if($artist->featuredImages()->first())
                        <img src="{{ $artist->featuredImages()->first()->image_url }}"
                             alt="{{ $artist->display_name }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-24 h-24 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Artist Info -->
                <div class="text-center md:text-left flex-1">
                    <h1 class="text-4xl font-bold mb-4">{{ $artist->display_name }}</h1>

                    @if($artist->specialties)
                        <div class="flex flex-wrap justify-center md:justify-start gap-2 mb-4">
                            @foreach($artist->specialties as $specialty)
                                <span class="inline-block px-3 py-1 text-sm font-medium rounded-full bg-white/10 text-white/90">
                                    {{ $specialty }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    @if($artist->bio)
                        <p class="text-gray-300 max-w-2xl mb-6">{{ $artist->bio }}</p>
                    @endif

                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4">
                        @if($artist->instagram_url)
                            <a href="{{ $artist->instagram_url }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="inline-flex items-center text-white/80 hover:text-white transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                                @{{ ltrim($artist->instagram_handle, '@') }}
                            </a>
                        @endif

                        @if($artist->hourly_rate)
                            <span class="text-white/80">
                                <span class="font-semibold">${{ number_format($artist->hourly_rate, 0) }}</span>/hour
                            </span>
                        @endif

                        @if($artist->is_accepting_bookings)
                            <span class="inline-flex items-center text-green-400">
                                <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                                Accepting bookings
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Book Button -->
                @if($artist->is_accepting_bookings)
                    <div class="flex-shrink-0">
                        <a href="{{ route('booking') }}?artist={{ $artist->slug }}"
                           class="inline-flex items-center justify-center px-6 py-3 text-base font-semibold rounded-lg text-white transition-all hover:opacity-90"
                           style="background-color: var(--color-primary)">
                            Book with {{ $artist->display_name }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section class="py-12 md:py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <h2 class="text-2xl font-bold" style="color: var(--color-primary)">Portfolio</h2>

                <!-- Style Filter -->
                @if(count($availableStyles) > 0)
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-medium text-gray-600">Filter:</span>
                        <button wire:click="clearFilter"
                                class="px-3 py-1.5 text-sm font-medium rounded-full transition-colors {{ $selectedStyle === '' ? 'text-white' : 'bg-white text-gray-600 hover:bg-gray-100' }}"
                                style="{{ $selectedStyle === '' ? 'background-color: var(--color-primary)' : '' }}">
                            All
                        </button>
                        @foreach($availableStyles as $style)
                            <button wire:click="filterByStyle('{{ $style }}')"
                                    class="px-3 py-1.5 text-sm font-medium rounded-full transition-colors {{ $selectedStyle === $style ? 'text-white' : 'bg-white text-gray-600 hover:bg-gray-100' }}"
                                    style="{{ $selectedStyle === $style ? 'background-color: var(--color-primary)' : '' }}">
                                {{ $style }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            @if($portfolioImages->isEmpty())
                <div class="text-center py-12 bg-white rounded-lg">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No portfolio images yet</h3>
                    <p class="text-gray-500">Check back soon for {{ $artist->display_name }}'s latest work.</p>
                </div>
            @else
                <!-- Portfolio Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($portfolioImages as $image)
                        <button wire:click="openImage({{ $image->id }})"
                                class="group relative aspect-square overflow-hidden rounded-lg bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2"
                                style="--tw-ring-color: var(--color-primary)">
                            <img src="{{ $image->image_url }}"
                                 alt="{{ $image->title ?? 'Portfolio image' }}"
                                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors duration-300 flex items-center justify-center">
                                <svg class="w-10 h-10 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                </svg>
                            </div>
                            @if($image->is_featured)
                                <div class="absolute top-2 right-2 px-2 py-1 text-xs font-medium rounded bg-yellow-500 text-white">
                                    Featured
                                </div>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- Image Modal -->
    @if($showModal && $selectedImage)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <!-- Background overlay -->
                <div wire:click="closeModal" class="fixed inset-0 bg-black/80 transition-opacity"></div>

                <!-- Modal panel -->
                <div class="relative bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-4xl sm:w-full">
                    <!-- Close button -->
                    <button wire:click="closeModal"
                            class="absolute top-4 right-4 z-10 p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Navigation arrows -->
                    <button wire:click="previousImage"
                            class="absolute left-4 top-1/2 -translate-y-1/2 z-10 p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button wire:click="nextImage"
                            class="absolute right-4 top-1/2 -translate-y-1/2 z-10 p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <!-- Image -->
                    <div class="aspect-square sm:aspect-video bg-gray-900">
                        <img src="{{ $selectedImage->image_url }}"
                             alt="{{ $selectedImage->title ?? 'Portfolio image' }}"
                             class="w-full h-full object-contain">
                    </div>

                    <!-- Image info -->
                    @if($selectedImage->title || $selectedImage->description || $selectedImage->style)
                        <div class="p-6 bg-white">
                            @if($selectedImage->title)
                                <h3 class="text-xl font-semibold mb-2" style="color: var(--color-primary)">
                                    {{ $selectedImage->title }}
                                </h3>
                            @endif
                            @if($selectedImage->description)
                                <p class="text-gray-600 mb-3">{{ $selectedImage->description }}</p>
                            @endif
                            @if($selectedImage->style)
                                <span class="inline-block px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-600">
                                    {{ $selectedImage->style }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Back to Artists -->
    <section class="py-8 bg-white border-t">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('artists.index') }}"
               class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                </svg>
                Back to All Artists
            </a>
        </div>
    </section>
</div>
