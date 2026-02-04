<div class="max-w-2xl mx-auto">
    {{-- Success Header --}}
    <div class="text-center mb-8">
        <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center" style="background-color: var(--color-primary)">
            <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Booking Confirmed!</h1>
        <p class="text-gray-600">Your appointment request has been submitted successfully.</p>
    </div>

    {{-- Confirmation Number --}}
    <div class="bg-gray-100 rounded-lg p-4 text-center mb-8">
        <p class="text-sm text-gray-600 mb-1">Confirmation Number</p>
        <p class="text-2xl font-bold text-gray-900 font-mono">{{ $this->confirmationNumber }}</p>
    </div>

    {{-- Appointment Details Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Appointment Details</h2>

            <dl class="space-y-4">
                <div class="flex items-start">
                    <dt class="flex-shrink-0 w-8">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </dt>
                    <dd class="ml-3">
                        <p class="text-sm text-gray-500">Date & Time</p>
                        <p class="font-medium text-gray-900">
                            {{ $appointment->scheduled_at->format('l, F j, Y') }}
                        </p>
                        <p class="text-gray-700">
                            {{ $appointment->scheduled_at->format('g:i A') }} - {{ $appointment->ends_at->format('g:i A') }}
                            <span class="text-gray-500">({{ $appointment->formatted_duration }})</span>
                        </p>
                    </dd>
                </div>

                <div class="flex items-start">
                    <dt class="flex-shrink-0 w-8">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </dt>
                    <dd class="ml-3">
                        <p class="text-sm text-gray-500">Artist</p>
                        <p class="font-medium text-gray-900">{{ $appointment->artist->display_name }}</p>
                    </dd>
                </div>

                @if ($appointment->service)
                    <div class="flex items-start">
                        <dt class="flex-shrink-0 w-8">
                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </dt>
                        <dd class="ml-3">
                            <p class="text-sm text-gray-500">Service</p>
                            <p class="font-medium text-gray-900">{{ $appointment->service->name }}</p>
                        </dd>
                    </div>
                @endif

                @if ($studio->address)
                    <div class="flex items-start">
                        <dt class="flex-shrink-0 w-8">
                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </dt>
                        <dd class="ml-3">
                            <p class="text-sm text-gray-500">Location</p>
                            <p class="font-medium text-gray-900">{{ $studio->name }}</p>
                            <p class="text-gray-700">{{ $studio->address }}</p>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Deposit Status --}}
        @if ($appointment->deposit_amount > 0)
            <div class="px-6 py-4 border-t bg-gray-50">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500">Deposit</p>
                        <p class="font-semibold text-gray-900">${{ number_format($appointment->deposit_amount, 2) }}</p>
                    </div>
                    @if ($this->depositIsPaid)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Paid
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Pending
                        </span>
                    @endif
                </div>
            </div>
        @endif

        {{-- Status Banner --}}
        <div class="px-6 py-4 border-t" style="background-color: rgba(var(--color-primary-rgb, 0, 0, 0), 0.05)">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" style="color: var(--color-primary)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-gray-700">
                    <strong>Status:</strong>
                    @if ($appointment->status === 'pending')
                        Your booking is awaiting confirmation from the studio. You'll receive an email once it's confirmed.
                    @elseif ($appointment->status === 'confirmed')
                        Your appointment is confirmed! We'll see you soon.
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Add to Calendar --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <h3 class="font-semibold text-gray-900 mb-4">Add to Calendar</h3>
        <div class="flex flex-wrap gap-3">
            <a
                href="{{ $this->googleCalendarUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            >
                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zm0-2a8 8 0 100-16 8 8 0 000 16zm1-13h-2v6l5.25 3.15.75-1.23-4-2.42V7z"/>
                </svg>
                Google Calendar
            </a>
            <a
                href="{{ $this->outlookCalendarUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            >
                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7.88 12.04q0 .45-.11.87-.1.41-.33.74-.22.33-.58.52-.37.2-.87.2t-.85-.2q-.35-.21-.57-.55-.22-.33-.33-.75-.1-.42-.1-.86t.1-.87q.1-.43.34-.76.22-.34.59-.54.36-.2.87-.2t.86.2q.35.21.57.55.22.34.31.77.1.43.1.88zM24 12v9.38q0 .46-.33.8-.33.32-.8.32H7.13q-.46 0-.8-.33-.32-.33-.32-.8V18H1q-.41 0-.7-.3-.3-.29-.3-.7V7q0-.41.3-.7Q.58 6 1 6h6.5V2.55q0-.44.3-.75.3-.3.75-.3h12.9q.44 0 .75.3.3.3.3.75V12zm-6-8.25v3h3v-3zm0 4.5v3h3v-3zm0 4.5v1.83l3 1.67V12.75zm-5-8.25v3h3v-3zm0 4.5v3h3v-3zm0 4.5v3h3v-3zm-5-4.5h3v-3H8v3zm0 4.5h3v-3H8v3zm0 3.75V21h3v-3.75z"/>
                </svg>
                Outlook
            </a>
            <a
                href="{{ $this->appleCalendarUrl }}"
                download="appointment.ics"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            >
                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                </svg>
                Apple Calendar
            </a>
        </div>
    </div>

    {{-- Waiver Reminder --}}
    @if (!$this->hasWaiver)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-8">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-yellow-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="ml-4">
                    <h3 class="font-semibold text-yellow-800">Waiver Required</h3>
                    <p class="mt-1 text-sm text-yellow-700">
                        Please remember to complete the digital waiver before your appointment. A link will be sent to your email, or you can sign it when you arrive.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Contact Info --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Need to Make Changes?</h3>
        <p class="text-gray-600 text-sm mb-4">
            If you need to reschedule or cancel your appointment, please contact the studio directly.
        </p>
        <div class="flex flex-wrap gap-4">
            @if ($studio->phone)
                <a
                    href="tel:{{ $studio->phone }}"
                    class="inline-flex items-center text-sm font-medium hover:underline"
                    style="color: var(--color-primary)"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    {{ $studio->phone }}
                </a>
            @endif
            @if ($studio->email)
                <a
                    href="mailto:{{ $studio->email }}"
                    class="inline-flex items-center text-sm font-medium hover:underline"
                    style="color: var(--color-primary)"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    {{ $studio->email }}
                </a>
            @endif
        </div>
    </div>

    {{-- Back to Home --}}
    <div class="mt-8 text-center">
        <a
            href="{{ route('home') }}"
            class="inline-flex items-center text-sm font-medium hover:underline"
            style="color: var(--color-primary)"
        >
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Return to Home
        </a>
    </div>
</div>
