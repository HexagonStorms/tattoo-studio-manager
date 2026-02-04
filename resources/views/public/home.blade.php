<x-layouts.public>
    <x-slot:meta>
        <x-seo-meta
            :title="null"
            :description="'Welcome to ' . $studio->name . ' - Professional tattoo studio'"
        />
    </x-slot:meta>

    {{-- Use Livewire component for dynamic content --}}
    <livewire:public.home-page />
</x-layouts.public>
