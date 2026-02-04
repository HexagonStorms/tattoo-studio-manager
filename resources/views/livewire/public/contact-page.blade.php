<div>
    <!-- Page Header -->
    <section class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Contact Us</h1>
            <p class="text-xl text-gray-300 max-w-2xl mx-auto">
                Have questions? We'd love to hear from you.
            </p>
        </div>
    </section>

    <!-- Contact Content -->
    <section class="py-12 md:py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Contact Information -->
                <div>
                    <h2 class="text-2xl font-bold mb-6" style="color: var(--color-primary)">
                        Get In Touch
                    </h2>

                    <div class="space-y-6">
                        <!-- Address -->
                        @if($studio->address)
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" style="background-color: var(--color-primary)">
                                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Address</h3>
                                    <p class="text-gray-600">{{ $studio->address }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Phone -->
                        @if($studio->phone)
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" style="background-color: var(--color-primary)">
                                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Phone</h3>
                                    <a href="tel:{{ $studio->phone }}" class="text-gray-600 hover:text-gray-900 transition-colors">
                                        {{ $studio->phone }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- Email -->
                        @if($studio->email)
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" style="background-color: var(--color-primary)">
                                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Email</h3>
                                    <a href="mailto:{{ $studio->email }}" class="text-gray-600 hover:text-gray-900 transition-colors">
                                        {{ $studio->email }}
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Business Hours -->
                    <div class="mt-10">
                        <h2 class="text-2xl font-bold mb-6" style="color: var(--color-primary)">
                            Business Hours
                        </h2>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <dl class="space-y-3">
                                @foreach($businessHours as $day => $hours)
                                    <div class="flex justify-between">
                                        <dt class="font-medium text-gray-900">{{ $day }}</dt>
                                        <dd class="text-gray-600">{{ $hours }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    </div>

                    <!-- Map Placeholder -->
                    @if($studio->address)
                        <div class="mt-10">
                            <h2 class="text-2xl font-bold mb-6" style="color: var(--color-primary)">
                                Find Us
                            </h2>
                            <div class="bg-gray-200 rounded-lg h-64 flex items-center justify-center">
                                <div class="text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                    </svg>
                                    <p class="text-sm">{{ $studio->address }}</p>
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($studio->address) }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="inline-flex items-center mt-3 text-sm font-medium hover:underline"
                                       style="color: var(--color-primary)">
                                        Open in Google Maps
                                        <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Contact Form -->
                <div>
                    <h2 class="text-2xl font-bold mb-6" style="color: var(--color-primary)">
                        Send Us a Message
                    </h2>

                    @if($submitted)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                            <svg class="w-12 h-12 text-green-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-lg font-semibold text-green-800 mb-2">Message Sent!</h3>
                            <p class="text-green-700">Thank you for reaching out. We'll get back to you as soon as possible.</p>
                            <button wire:click="$set('submitted', false)"
                                    class="mt-4 text-sm font-medium hover:underline"
                                    style="color: var(--color-primary)">
                                Send another message
                            </button>
                        </div>
                    @else
                        <form wire:submit="submit" class="bg-white rounded-lg shadow-sm p-6 space-y-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Your Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="name"
                                       wire:model="name"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition-colors @error('name') border-red-500 @enderror"
                                       style="--tw-ring-color: var(--color-primary)"
                                       placeholder="John Doe">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email"
                                       id="email"
                                       wire:model="email"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition-colors @error('email') border-red-500 @enderror"
                                       style="--tw-ring-color: var(--color-primary)"
                                       placeholder="john@example.com">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Message -->
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">
                                    Message <span class="text-red-500">*</span>
                                </label>
                                <textarea id="message"
                                          wire:model="message"
                                          rows="5"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent transition-colors resize-none @error('message') border-red-500 @enderror"
                                          style="--tw-ring-color: var(--color-primary)"
                                          placeholder="Tell us about your tattoo idea, questions, or how we can help..."></textarea>
                                @error('message')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <button type="submit"
                                    class="w-full px-6 py-3 text-white font-semibold rounded-lg transition-colors hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed"
                                    style="background-color: var(--color-primary)"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove>Send Message</span>
                                <span wire:loading class="inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Sending...
                                </span>
                            </button>
                        </form>
                    @endif

                    <!-- Alternative CTA -->
                    <div class="mt-8 text-center">
                        <p class="text-gray-600 mb-4">Ready to book your appointment?</p>
                        <a href="{{ route('booking') }}"
                           class="inline-flex items-center justify-center px-6 py-3 border-2 font-semibold rounded-lg transition-colors hover:text-white"
                           style="border-color: var(--color-primary); color: var(--color-primary)"
                           onmouseover="this.style.backgroundColor=getComputedStyle(document.documentElement).getPropertyValue('--color-primary')"
                           onmouseout="this.style.backgroundColor='transparent'">
                            Book Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
