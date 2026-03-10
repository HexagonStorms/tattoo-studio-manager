@php
    $tenantService = app(\App\Services\TenantService::class);
    $studio = $tenantService->current();
    $logoUrl = $tenantService->logoUrl();
    $colors = $tenantService->colors();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{ $meta ?? '' }}

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Tenant-specific CSS variables --}}
    <style>
        :root {
            @foreach($colors as $property => $value)
                {{ $property }}: {{ $value }};
            @endforeach
        }
    </style>

    {{ $head ?? '' }}
</head>
<body class="min-h-screen bg-gray-50 font-sans antialiased">
    {{-- Platform Admin Toolbar --}}
    @auth
        @if(auth()->user()->isPlatformAdmin())
            @livewire('platform-admin-toolbar')
        @endif
    @endauth

    {{-- Navigation --}}
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                {{-- Logo / Brand --}}
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center space-x-3">
                        @if($logoUrl)
                            <img
                                src="{{ $logoUrl }}"
                                alt="{{ $studio->name }}"
                                class="h-10 w-auto"
                            >
                        @endif
                        <span class="text-xl font-bold text-gray-900" style="color: var(--color-primary)">
                            {{ $studio->name }}
                        </span>
                    </a>
                </div>

                {{-- Desktop Navigation --}}
                <div class="hidden sm:flex sm:items-center sm:space-x-8">
                    <a
                        href="{{ route('home') }}"
                        class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('home') ? 'text-gray-900 border-b-2' : '' }}"
                        style="{{ request()->routeIs('home') ? 'border-color: var(--color-primary)' : '' }}"
                    >
                        Home
                    </a>
                    <a
                        href="{{ route('artists.index') }}"
                        class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('artists.*') ? 'text-gray-900 border-b-2' : '' }}"
                        style="{{ request()->routeIs('artists.*') ? 'border-color: var(--color-primary)' : '' }}"
                    >
                        Artists
                    </a>
                    <a
                        href="{{ route('booking') }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-md shadow-sm transition-colors hover:opacity-90"
                        style="background-color: var(--color-primary)"
                    >
                        Book Now
                    </a>
                    <a
                        href="{{ route('contact') }}"
                        class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('contact') ? 'text-gray-900 border-b-2' : '' }}"
                        style="{{ request()->routeIs('contact') ? 'border-color: var(--color-primary)' : '' }}"
                    >
                        Contact
                    </a>
                </div>

                {{-- Mobile menu button --}}
                <div class="flex items-center sm:hidden">
                    <button
                        type="button"
                        class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gray-500"
                        aria-expanded="false"
                    >
                        <span class="sr-only">Open main menu</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Navigation Menu --}}
        <div class="mobile-menu hidden sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
                <a
                    href="{{ route('home') }}"
                    class="block pl-3 pr-4 py-2 text-base font-medium {{ request()->routeIs('home') ? 'bg-gray-50 border-l-4 text-gray-900' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50' }}"
                    style="{{ request()->routeIs('home') ? 'border-color: var(--color-primary)' : '' }}"
                >
                    Home
                </a>
                <a
                    href="{{ route('artists.index') }}"
                    class="block pl-3 pr-4 py-2 text-base font-medium {{ request()->routeIs('artists.*') ? 'bg-gray-50 border-l-4 text-gray-900' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50' }}"
                    style="{{ request()->routeIs('artists.*') ? 'border-color: var(--color-primary)' : '' }}"
                >
                    Artists
                </a>
                <a
                    href="{{ route('booking') }}"
                    class="block pl-3 pr-4 py-2 text-base font-medium {{ request()->routeIs('booking') ? 'bg-gray-50 border-l-4 text-gray-900' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50' }}"
                    style="{{ request()->routeIs('booking') ? 'border-color: var(--color-primary)' : '' }}"
                >
                    Book Now
                </a>
                <a
                    href="{{ route('contact') }}"
                    class="block pl-3 pr-4 py-2 text-base font-medium {{ request()->routeIs('contact') ? 'bg-gray-50 border-l-4 text-gray-900' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50' }}"
                    style="{{ request()->routeIs('contact') ? 'border-color: var(--color-primary)' : '' }}"
                >
                    Contact
                </a>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Studio Info --}}
                <div>
                    <h3 class="text-lg font-semibold mb-4">{{ $studio->name }}</h3>
                    @if($studio->address)
                        <p class="text-gray-400 text-sm mb-2">
                            {{ $studio->address }}
                        </p>
                    @endif
                    @if($studio->phone)
                        <p class="text-gray-400 text-sm mb-2">
                            <a href="tel:{{ $studio->phone }}" class="hover:text-white transition-colors">
                                {{ $studio->phone }}
                            </a>
                        </p>
                    @endif
                    @if($studio->email)
                        <p class="text-gray-400 text-sm">
                            <a href="mailto:{{ $studio->email }}" class="hover:text-white transition-colors">
                                {{ $studio->email }}
                            </a>
                        </p>
                    @endif
                </div>

                {{-- Quick Links --}}
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('home') }}" class="text-gray-400 hover:text-white text-sm transition-colors">
                                Home
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('artists.index') }}" class="text-gray-400 hover:text-white text-sm transition-colors">
                                Our Artists
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('booking') }}" class="text-gray-400 hover:text-white text-sm transition-colors">
                                Book an Appointment
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('contact') }}" class="text-gray-400 hover:text-white text-sm transition-colors">
                                Contact Us
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Hours / Additional Info --}}
                <div>
                    <h3 class="text-lg font-semibold mb-4">Connect</h3>
                    <p class="text-gray-400 text-sm mb-4">
                        Follow us on social media for the latest tattoo designs and studio updates.
                    </p>
                    <a
                        href="{{ route('booking') }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-md transition-colors hover:opacity-90"
                        style="background-color: var(--color-primary)"
                    >
                        Book Your Appointment
                    </a>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-500 text-sm">
                <p>&copy; {{ date('Y') }} {{ $studio->name }}. All rights reserved.</p>
            </div>
        </div>
    </footer>

    {{-- Mobile menu toggle script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const mobileMenu = document.querySelector('.mobile-menu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                    const expanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
                    mobileMenuButton.setAttribute('aria-expanded', !expanded);
                });
            }
        });
    </script>

    @livewireScripts
    {{ $scripts ?? '' }}
</body>
</html>
