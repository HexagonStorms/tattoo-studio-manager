<div class="max-w-4xl mx-auto">
    {{-- Progress Indicator --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            @foreach ([
                1 => 'Artist',
                2 => 'Service',
                3 => 'Date & Time',
                4 => 'Details',
                5 => 'Review',
            ] as $step => $label)
                <div class="flex items-center {{ $step < 5 ? 'flex-1' : '' }}">
                    <button
                        wire:click="goToStep({{ $step }})"
                        @class([
                            'w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold transition-all',
                            'bg-green-500 text-white' => $currentStep > $step,
                            'text-white' => $currentStep === $step,
                            'bg-gray-200 text-gray-500' => $currentStep < $step,
                        ])
                        style="{{ $currentStep === $step ? 'background-color: var(--color-primary)' : '' }}"
                        {{ $currentStep < $step ? 'disabled' : '' }}
                    >
                        @if ($currentStep > $step)
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        @else
                            {{ $step }}
                        @endif
                    </button>
                    <span class="ml-2 text-sm {{ $currentStep >= $step ? 'text-gray-900 font-medium' : 'text-gray-400' }} hidden sm:inline">
                        {{ $label }}
                    </span>
                    @if ($step < 5)
                        <div class="flex-1 h-0.5 mx-4 {{ $currentStep > $step ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Error Message --}}
    @if ($errorMessage)
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="ml-3 text-sm text-red-700">{{ $errorMessage }}</p>
            </div>
        </div>
    @endif

    {{-- Step Content --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">
        {{-- Step 1: Select Artist --}}
        @if ($currentStep === 1)
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Select Your Artist</h2>
                <p class="text-gray-600 mb-6">Choose the artist you'd like to work with, or let us match you with the first available.</p>

                {{-- Any Available Artist Option --}}
                <button
                    wire:click="selectAnyArtist"
                    @class([
                        'w-full p-4 rounded-lg border-2 mb-6 text-left transition-all',
                        'border-gray-300 hover:border-gray-400' => !$anyArtist,
                    ])
                    style="{{ $anyArtist ? 'border-color: var(--color-primary); background-color: rgba(var(--color-primary-rgb, 0, 0, 0), 0.05)' : '' }}"
                >
                    <div class="flex items-center">
                        <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center mr-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Any Available Artist</h3>
                            <p class="text-sm text-gray-500">We'll match you with the first available artist for your preferred time</p>
                        </div>
                        @if ($anyArtist)
                            <div class="ml-auto">
                                <svg class="w-6 h-6" style="color: var(--color-primary)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        @endif
                    </div>
                </button>

                {{-- Artist Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ($this->artists as $artist)
                        <button
                            wire:click="selectArtist({{ $artist->id }})"
                            @class([
                                'p-4 rounded-lg border-2 text-left transition-all hover:shadow-md',
                                'border-gray-200 hover:border-gray-300' => $selectedArtistId !== $artist->id,
                            ])
                            style="{{ $selectedArtistId === $artist->id ? 'border-color: var(--color-primary); background-color: rgba(var(--color-primary-rgb, 0, 0, 0), 0.05)' : '' }}"
                        >
                            <div class="flex items-start">
                                <div class="w-16 h-16 rounded-full bg-gray-200 overflow-hidden flex-shrink-0 mr-4">
                                    @if ($artist->portfolioImages->first())
                                        <img
                                            src="{{ \Illuminate\Support\Facades\Storage::url($artist->portfolioImages->first()->image_path) }}"
                                            alt="{{ $artist->display_name }}"
                                            class="w-full h-full object-cover"
                                        >
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900 truncate">{{ $artist->display_name }}</h3>
                                    @if ($artist->specialties)
                                        <p class="text-sm text-gray-500 truncate">
                                            {{ implode(', ', array_slice($artist->specialties, 0, 2)) }}
                                        </p>
                                    @endif
                                    @if ($artist->hourly_rate)
                                        <p class="text-sm font-medium mt-1" style="color: var(--color-primary)">
                                            ${{ number_format($artist->hourly_rate, 0) }}/hr
                                        </p>
                                    @endif
                                </div>
                                @if ($selectedArtistId === $artist->id)
                                    <div class="ml-2">
                                        <svg class="w-6 h-6" style="color: var(--color-primary)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Step 2: Select Service --}}
        @if ($currentStep === 2)
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Select a Service</h2>
                <p class="text-gray-600 mb-6">Choose the type of tattoo work you're interested in. This helps us estimate time and pricing.</p>

                {{-- Services List --}}
                <div class="space-y-4">
                    @forelse ($this->services as $service)
                        <button
                            wire:click="selectService({{ $service->id }})"
                            @class([
                                'w-full p-4 rounded-lg border-2 text-left transition-all hover:shadow-md',
                                'border-gray-200 hover:border-gray-300' => $selectedServiceId !== $service->id,
                            ])
                            style="{{ $selectedServiceId === $service->id ? 'border-color: var(--color-primary); background-color: rgba(var(--color-primary-rgb, 0, 0, 0), 0.05)' : '' }}"
                        >
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900">{{ $service->name }}</h3>
                                    @if ($service->description)
                                        <p class="text-sm text-gray-500 mt-1">{{ $service->description }}</p>
                                    @endif
                                    <div class="flex items-center gap-4 mt-2 text-sm">
                                        <span class="text-gray-600">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $service->formatted_duration }}
                                        </span>
                                        <span class="font-medium" style="color: var(--color-primary)">
                                            {{ $service->formatted_price }}
                                        </span>
                                    </div>
                                </div>
                                @if ($selectedServiceId === $service->id)
                                    <div class="ml-4">
                                        <svg class="w-6 h-6" style="color: var(--color-primary)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </button>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <p>No services available. You can still book a consultation.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Skip Service Option --}}
                <div class="mt-6 pt-6 border-t">
                    <button
                        wire:click="$set('selectedServiceId', null)"
                        class="text-sm text-gray-500 hover:text-gray-700 underline"
                    >
                        Skip - I'd like to discuss options during consultation
                    </button>
                </div>
            </div>
        @endif

        {{-- Step 3: Select Date & Time --}}
        @if ($currentStep === 3)
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Choose Date & Time</h2>
                <p class="text-gray-600 mb-6">
                    Select your preferred appointment date and time.
                    @if ($this->selectedArtist)
                        Showing availability for <strong>{{ $this->selectedArtist->display_name }}</strong>.
                    @endif
                </p>

                <div class="grid md:grid-cols-2 gap-8">
                    {{-- Calendar --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select a Date</label>
                        <input
                            type="date"
                            wire:model.live="selectedDate"
                            min="{{ $this->minimumDate }}"
                            max="{{ now()->addMonths(3)->format('Y-m-d') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                            style="--tw-ring-color: var(--color-primary)"
                        >

                        @if ($selectedDate && $this->workingHours)
                            <p class="mt-2 text-sm text-gray-500">
                                Working hours: {{ $this->workingHours['start'] }} - {{ $this->workingHours['end'] }}
                            </p>
                        @elseif ($selectedDate && !$this->workingHours)
                            <p class="mt-2 text-sm text-red-500">
                                The artist is not available on this day. Please select another date.
                            </p>
                        @endif
                    </div>

                    {{-- Time Slots --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select a Time</label>

                        @if (!$selectedDate)
                            <div class="bg-gray-50 rounded-lg p-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p>Select a date to see available times</p>
                            </div>
                        @elseif (empty($this->timeSlots))
                            <div class="bg-gray-50 rounded-lg p-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p>No available times for this date</p>
                                <p class="text-sm mt-1">Try selecting a different date</p>
                            </div>
                        @else
                            <div class="grid grid-cols-3 gap-2 max-h-64 overflow-y-auto p-1">
                                @foreach ($this->timeSlots as $slot)
                                    <button
                                        wire:click="selectTime('{{ $slot['time'] }}', '{{ $slot['datetime'] }}')"
                                        @class([
                                            'px-3 py-2 text-sm rounded-lg border transition-all',
                                            'border-gray-200 text-gray-400 cursor-not-allowed' => !$slot['available'],
                                            'border-gray-300 hover:border-gray-400 text-gray-700' => $slot['available'] && $selectedTime !== $slot['time'],
                                            'text-white' => $selectedTime === $slot['time'],
                                        ])
                                        style="{{ $selectedTime === $slot['time'] ? 'background-color: var(--color-primary); border-color: var(--color-primary)' : '' }}"
                                        {{ !$slot['available'] ? 'disabled' : '' }}
                                    >
                                        {{ $slot['display'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                @if ($selectedDate && $selectedTime)
                    <div class="mt-6 p-4 rounded-lg" style="background-color: rgba(var(--color-primary-rgb, 0, 0, 0), 0.05)">
                        <p class="text-sm">
                            <strong>Selected:</strong>
                            {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }} at {{ \Carbon\Carbon::parse($selectedTime)->format('g:i A') }}
                            <span class="text-gray-500">({{ $this->duration }} min appointment)</span>
                        </p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Step 4: Your Details --}}
        @if ($currentStep === 4)
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Your Details</h2>
                <p class="text-gray-600 mb-6">Tell us about yourself and your tattoo idea.</p>

                <div class="space-y-6">
                    {{-- Contact Info --}}
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label for="clientName" class="block text-sm font-medium text-gray-700 mb-1">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="clientName"
                                wire:model="clientName"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent @error('clientName') border-red-500 @enderror"
                                placeholder="Your full name"
                            >
                            @error('clientName')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="clientEmail" class="block text-sm font-medium text-gray-700 mb-1">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                id="clientEmail"
                                wire:model="clientEmail"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent @error('clientEmail') border-red-500 @enderror"
                                placeholder="you@example.com"
                            >
                            @error('clientEmail')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="clientPhone" class="block text-sm font-medium text-gray-700 mb-1">
                            Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="tel"
                            id="clientPhone"
                            wire:model="clientPhone"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent @error('clientPhone') border-red-500 @enderror"
                            placeholder="(555) 123-4567"
                        >
                        @error('clientPhone')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tattoo Info --}}
                    <div class="pt-6 border-t">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Tattoo Details</h3>

                        <div class="space-y-4">
                            <div>
                                <label for="tattooDescription" class="block text-sm font-medium text-gray-700 mb-1">
                                    Describe Your Tattoo Idea
                                </label>
                                <textarea
                                    id="tattooDescription"
                                    wire:model="tattooDescription"
                                    rows="4"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                                    placeholder="Describe the tattoo you have in mind - style, subject matter, size, colors, etc."
                                ></textarea>
                            </div>

                            <div>
                                <label for="tattooPlacement" class="block text-sm font-medium text-gray-700 mb-1">
                                    Placement
                                </label>
                                <select
                                    id="tattooPlacement"
                                    wire:model="tattooPlacement"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                                >
                                    <option value="">Select placement...</option>
                                    <option value="arm_upper">Upper Arm</option>
                                    <option value="arm_lower">Lower Arm / Forearm</option>
                                    <option value="arm_full">Full Arm / Sleeve</option>
                                    <option value="wrist">Wrist</option>
                                    <option value="hand">Hand</option>
                                    <option value="finger">Finger</option>
                                    <option value="shoulder">Shoulder</option>
                                    <option value="back_upper">Upper Back</option>
                                    <option value="back_lower">Lower Back</option>
                                    <option value="back_full">Full Back</option>
                                    <option value="chest">Chest</option>
                                    <option value="ribs">Ribs / Side</option>
                                    <option value="stomach">Stomach</option>
                                    <option value="leg_upper">Upper Leg / Thigh</option>
                                    <option value="leg_lower">Lower Leg / Calf</option>
                                    <option value="leg_full">Full Leg</option>
                                    <option value="ankle">Ankle</option>
                                    <option value="foot">Foot</option>
                                    <option value="neck">Neck</option>
                                    <option value="behind_ear">Behind Ear</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                    Special Requests or Notes
                                </label>
                                <textarea
                                    id="notes"
                                    wire:model="notes"
                                    rows="2"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
                                    placeholder="Any allergies, accessibility needs, or other information we should know?"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 5: Review & Confirm --}}
        @if ($currentStep === 5)
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Review Your Booking</h2>
                <p class="text-gray-600 mb-6">Please review your booking details before confirming.</p>

                {{-- Booking Summary --}}
                <div class="space-y-6">
                    {{-- Appointment Details --}}
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Appointment Details</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Date:</dt>
                                <dd class="font-medium text-gray-900">
                                    {{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') : '-' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Time:</dt>
                                <dd class="font-medium text-gray-900">
                                    {{ $selectedTime ? \Carbon\Carbon::parse($selectedTime)->format('g:i A') : '-' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Duration:</dt>
                                <dd class="font-medium text-gray-900">{{ $this->duration }} minutes</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Artist:</dt>
                                <dd class="font-medium text-gray-900">
                                    {{ $anyArtist ? 'Any Available Artist' : ($this->selectedArtist?->display_name ?? '-') }}
                                </dd>
                            </div>
                            @if ($this->selectedService)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Service:</dt>
                                    <dd class="font-medium text-gray-900">{{ $this->selectedService->name }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    {{-- Contact Info --}}
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Your Information</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Name:</dt>
                                <dd class="font-medium text-gray-900">{{ $clientName }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Email:</dt>
                                <dd class="font-medium text-gray-900">{{ $clientEmail }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Phone:</dt>
                                <dd class="font-medium text-gray-900">{{ $clientPhone }}</dd>
                            </div>
                        </dl>
                    </div>

                    @if ($tattooDescription || $tattooPlacement)
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="font-semibold text-gray-900 mb-4">Tattoo Details</h3>
                            @if ($tattooPlacement)
                                <p class="text-sm text-gray-600 mb-2">
                                    <strong>Placement:</strong> {{ ucwords(str_replace('_', ' ', $tattooPlacement)) }}
                                </p>
                            @endif
                            @if ($tattooDescription)
                                <p class="text-sm text-gray-700">{{ $tattooDescription }}</p>
                            @endif
                        </div>
                    @endif

                    {{-- Deposit Info --}}
                    @if ($this->depositAmount > 0)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                            <h3 class="font-semibold text-gray-900 mb-2">Deposit Required</h3>
                            <p class="text-gray-600 text-sm mb-3">
                                A deposit is required to secure your booking. This will be applied to your final tattoo cost.
                            </p>
                            <p class="text-2xl font-bold" style="color: var(--color-primary)">
                                ${{ number_format($this->depositAmount, 2) }}
                            </p>
                        </div>
                    @endif

                    {{-- Booking Instructions --}}
                    @if ($studio->booking_instructions)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                            <h3 class="font-semibold text-gray-900 mb-2">Important Information</h3>
                            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $studio->booking_instructions }}</p>
                        </div>
                    @endif

                    {{-- Terms Acceptance --}}
                    <div class="pt-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:model="termsAccepted"
                                class="mt-1 w-5 h-5 rounded border-gray-300 focus:ring-2"
                                style="--tw-ring-color: var(--color-primary)"
                            >
                            <span class="text-sm text-gray-600">
                                I agree to the studio's booking policies and understand that deposits are non-refundable for no-shows or cancellations within 24 hours of the appointment.
                            </span>
                        </label>
                        @error('termsAccepted')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        @endif

        {{-- Navigation Buttons --}}
        <div class="mt-8 pt-6 border-t flex justify-between">
            @if ($currentStep > 1)
                <button
                    wire:click="previousStep"
                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Back
                </button>
            @else
                <div></div>
            @endif

            @if ($currentStep < 5)
                <button
                    wire:click="nextStep"
                    class="px-6 py-2 text-white rounded-lg transition-colors hover:opacity-90"
                    style="background-color: var(--color-primary)"
                >
                    Continue
                </button>
            @else
                <button
                    wire:click="submitBooking"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    class="px-8 py-3 text-white rounded-lg font-semibold transition-colors hover:opacity-90 flex items-center gap-2"
                    style="background-color: var(--color-primary)"
                    {{ !$termsAccepted ? 'disabled' : '' }}
                >
                    <span wire:loading.remove wire:target="submitBooking">
                        @if ($this->depositAmount > 0)
                            Request Booking
                        @else
                            Confirm Booking
                        @endif
                    </span>
                    <span wire:loading wire:target="submitBooking">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <span wire:loading wire:target="submitBooking">Processing...</span>
                </button>
            @endif
        </div>
    </div>
</div>
