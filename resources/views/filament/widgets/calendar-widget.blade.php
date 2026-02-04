<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Header with navigation --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <x-filament::button
                    size="sm"
                    color="gray"
                    wire:click="previousWeek"
                    icon="heroicon-m-chevron-left"
                />
                <x-filament::button
                    size="sm"
                    color="gray"
                    wire:click="today"
                >
                    Today
                </x-filament::button>
                <x-filament::button
                    size="sm"
                    color="gray"
                    wire:click="nextWeek"
                    icon="heroicon-m-chevron-right"
                />
            </div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $this->getWeekRange() }}
            </h2>
        </div>

        {{-- Calendar grid --}}
        <div class="grid grid-cols-7 gap-2">
            @foreach ($this->getWeekDays() as $day)
                <div class="min-h-[150px] border rounded-lg overflow-hidden {{ $day['isToday'] ? 'border-primary-500 ring-1 ring-primary-500' : 'border-gray-200 dark:border-gray-700' }} {{ $day['isPast'] ? 'opacity-60' : '' }}">
                    {{-- Day header --}}
                    <div class="px-2 py-1 text-center {{ $day['isToday'] ? 'bg-primary-500 text-white' : 'bg-gray-50 dark:bg-gray-800' }}">
                        <div class="text-xs font-medium {{ $day['isToday'] ? 'text-white' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ $day['dayName'] }}
                        </div>
                        <div class="text-lg font-semibold {{ $day['isToday'] ? 'text-white' : 'text-gray-900 dark:text-white' }}">
                            {{ $day['dayNumber'] }}
                        </div>
                    </div>

                    {{-- Appointments --}}
                    <div class="p-1 space-y-1 max-h-[120px] overflow-y-auto">
                        @forelse ($day['appointments'] as $appointment)
                            <a
                                href="{{ $appointment['url'] }}"
                                class="block p-1.5 rounded text-xs transition hover:ring-1 hover:ring-primary-500
                                    @switch($appointment['status_color'])
                                        @case('warning')
                                            bg-warning-50 dark:bg-warning-500/10 border-l-2 border-warning-500
                                            @break
                                        @case('success')
                                            bg-success-50 dark:bg-success-500/10 border-l-2 border-success-500
                                            @break
                                        @case('info')
                                            bg-info-50 dark:bg-info-500/10 border-l-2 border-info-500
                                            @break
                                        @default
                                            bg-gray-50 dark:bg-gray-700 border-l-2 border-gray-400
                                    @endswitch
                                "
                            >
                                <div class="font-medium text-gray-900 dark:text-white truncate">
                                    {{ $appointment['time'] }}
                                </div>
                                <div class="text-gray-600 dark:text-gray-300 truncate">
                                    {{ $appointment['client_name'] }}
                                </div>
                                <div class="text-gray-500 dark:text-gray-400 truncate text-[10px]">
                                    {{ $appointment['artist_name'] }}
                                </div>
                            </a>
                        @empty
                            <div class="text-center text-xs text-gray-400 dark:text-gray-500 py-4">
                                No appointments
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
