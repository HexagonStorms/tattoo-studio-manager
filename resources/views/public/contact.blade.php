<x-layouts.public>
    <x-slot:meta>
        <x-seo-meta
            title="Contact Us"
            :description="'Get in touch with ' . $studio->name . '. Find our location, hours, and contact information.'"
        />
    </x-slot:meta>

    {{-- Use Livewire component for contact form --}}
    <livewire:public.contact-page />
</x-layouts.public>
