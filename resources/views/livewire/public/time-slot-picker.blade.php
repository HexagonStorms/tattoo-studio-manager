<div class="space-y-6">
    {{-- Date Selection --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Select a Date</label>
        <input
            type="date"
            wire:model.live="selectedDate"
            min="{{ $this->minimumDate }}"
            max="{{ $this->maximumDate }}"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent"
            style="--tw-ring-color: var(--color-primary)"
        >

        @if ($selectedDate && $this->workingHours)
            <p class="mt-2 text-sm text-gray-500">
                <svg class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Working hours: {{ $this->workingHours['start'] }} - {{ $this->workingHours['end'] }}
            </p>
        @elseif ($selectedDate && !$this->workingHours)
            <p class="mt-2 text-sm text-red-500">
                <svg class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                The artist is not available on this day.
            </p>
        @endif
    </div>

    {{-- Time Slots --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Select a Time</label>

        @if (!$artistId)
            <div class="bg-gray-50 rounded-lg p-8 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <p>Select an artist first to see available times</p>
            </div>
        @elseif (!$selectedDate)
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
            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 max-h-64 overflow-y-auto p-1">
                @foreach ($this->timeSlots as $slot)
                    <button
                        type="button"
                        wire:click="selectTime('{{ $slot['time'] }}', '{{ $slot['datetime'] }}')"
                        @class([
                            'px-3 py-2 text-sm rounded-lg border transition-all',
                            'border-gray-200 text-gray-400 cursor-not-allowed' => !$slot['available'],
                            'border-gray-300 hover:border-gray-400 text-gray-700 hover:bg-gray-50' => $slot['available'] && $selectedTime !== $slot['time'],
                            'text-white' => $selectedTime === $slot['time'],
                        ])
                        style="{{ $selectedTime === $slot['time'] ? 'background-color: var(--color-primary); border-color: var(--color-primary)' : '' }}"
                        {{ !$slot['available'] ? 'disabled' : '' }}
                    >
                        {{ $slot['display'] }}
                    </button>
                @endforeach
            </div>

            <div class="mt-4 flex items-center gap-4 text-xs text-gray-500">
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded border border-gray-300 bg-white"></div>
                    <span>Available</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded border border-gray-200 bg-gray-100"></div>
                    <span>Unavailable</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 rounded" style="background-color: var(--color-primary)"></div>
                    <span>Selected</span>
                </div>
            </div>
        @endif
    </div>

    {{-- Selected Time Summary --}}
    @if ($selectedDate && $selectedTime)
        <div class="p-4 rounded-lg border-2" style="border-color: var(--color-primary); background-color: rgba(var(--color-primary-rgb, 0, 0, 0), 0.05)">
            <p class="text-sm font-medium text-gray-900">
                <svg class="w-5 h-5 inline mr-1" style="color: var(--color-primary)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Selected: {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }} at {{ \Carbon\Carbon::parse($selectedTime)->format('g:i A') }}
            </p>
        </div>
    @endif
</div>
