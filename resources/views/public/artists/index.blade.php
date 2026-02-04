<x-layouts.public>
    <x-slot:meta>
        <x-seo-meta
            title="Our Artists"
            :description="'Meet the talented tattoo artists at ' . $studio->name"
        />
    </x-slot:meta>

    {{-- Use Livewire component for interactive filtering --}}
    <livewire:public.artist-list />
</x-layouts.public>
