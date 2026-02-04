<div>
    <!-- Style Filter -->
    @if($filterable && count($availableStyles) > 0)
        <div class="flex flex-wrap items-center gap-2 mb-6">
            <span class="text-sm font-medium text-gray-600">Filter by style:</span>
            <button wire:click="clearFilter"
                    class="px-3 py-1.5 text-sm font-medium rounded-full transition-colors {{ $selectedStyle === '' ? 'text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                    style="{{ $selectedStyle === '' ? 'background-color: var(--color-primary)' : '' }}">
                All
            </button>
            @foreach($availableStyles as $style)
                <button wire:click="filterByStyle('{{ $style }}')"
                        class="px-3 py-1.5 text-sm font-medium rounded-full transition-colors {{ $selectedStyle === $style ? 'text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                        style="{{ $selectedStyle === $style ? 'background-color: var(--color-primary)' : '' }}">
                    {{ $style }}
                </button>
            @endforeach
        </div>
    @endif

    <!-- Gallery Grid -->
    @if($filteredImages->isEmpty())
        <div class="text-center py-12 bg-gray-100 rounded-lg">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-gray-500">No images to display.</p>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($filteredImages as $image)
                <button wire:click="openImage({{ $image->id }})"
                        wire:key="gallery-image-{{ $image->id }}"
                        class="group relative aspect-square overflow-hidden rounded-lg bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2"
                        style="--tw-ring-color: var(--color-primary)">
                    <img src="{{ $image->image_url }}"
                         alt="{{ $image->title ?? 'Portfolio image' }}"
                         loading="lazy"
                         class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">

                    <!-- Hover Overlay -->
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors duration-300 flex items-center justify-center">
                        <svg class="w-10 h-10 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                        </svg>
                    </div>

                    <!-- Featured Badge -->
                    @if($image->is_featured)
                        <div class="absolute top-2 right-2 px-2 py-1 text-xs font-medium rounded bg-yellow-500 text-white">
                            Featured
                        </div>
                    @endif

                    <!-- Style Badge -->
                    @if($image->style && !$filterable)
                        <div class="absolute bottom-2 left-2 px-2 py-1 text-xs font-medium rounded bg-black/60 text-white">
                            {{ $image->style }}
                        </div>
                    @endif
                </button>
            @endforeach
        </div>
    @endif

    <!-- Lightbox Modal -->
    @if($showModal && $selectedImage)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="gallery-modal" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <!-- Background overlay -->
                <div wire:click="closeModal" class="fixed inset-0 bg-black/80 transition-opacity"></div>

                <!-- Modal panel -->
                <div class="relative bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-4xl sm:w-full">
                    <!-- Close button -->
                    <button wire:click="closeModal"
                            class="absolute top-4 right-4 z-10 p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors focus:outline-none focus:ring-2 focus:ring-white">
                        <span class="sr-only">Close</span>
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Navigation: Previous -->
                    <button wire:click="previousImage"
                            class="absolute left-4 top-1/2 -translate-y-1/2 z-10 p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors focus:outline-none focus:ring-2 focus:ring-white">
                        <span class="sr-only">Previous image</span>
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <!-- Navigation: Next -->
                    <button wire:click="nextImage"
                            class="absolute right-4 top-1/2 -translate-y-1/2 z-10 p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors focus:outline-none focus:ring-2 focus:ring-white">
                        <span class="sr-only">Next image</span>
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
                        <div class="p-6 bg-white text-left">
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
</div>
