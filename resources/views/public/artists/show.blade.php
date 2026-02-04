<x-layouts.public>
    <x-slot:meta>
        <x-seo-meta
            :title="$artist->display_name"
            :description="$artist->bio ? Str::limit($artist->bio, 160) : 'Tattoo artist at ' . $studio->name"
            :image="$artist->portfolioImages->first() ? Storage::url($artist->portfolioImages->first()->image_path) : null"
        />
    </x-slot:meta>

    {{-- Use Livewire component for interactive portfolio with lightbox --}}
    <livewire:public.artist-profile :slug="$artist->slug" />
</x-layouts.public>
