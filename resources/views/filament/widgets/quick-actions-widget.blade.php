<x-filament-widgets::widget>
    <x-filament::section heading="Quick Actions">
        <div class="grid gap-4">
            @foreach ($this->getActions() as $action)
                @if ($action['enabled'])
                    <a
                        href="{{ $action['url'] }}"
                        class="flex items-center gap-4 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                    >
                        <div class="flex-shrink-0 p-3 rounded-lg bg-primary-50 dark:bg-primary-900/20">
                            <x-filament::icon
                                :icon="$action['icon']"
                                class="h-6 w-6 text-primary-600 dark:text-primary-400"
                            />
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ $action['label'] }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $action['description'] }}
                            </p>
                        </div>
                    </a>
                @else
                    <button
                        type="button"
                        wire:click="showComingSoon"
                        class="flex items-center gap-4 p-4 rounded-lg border border-gray-200 dark:border-gray-700 opacity-60 cursor-not-allowed text-left w-full"
                    >
                        <div class="flex-shrink-0 p-3 rounded-lg bg-gray-100 dark:bg-gray-800">
                            <x-filament::icon
                                :icon="$action['icon']"
                                class="h-6 w-6 text-gray-400 dark:text-gray-500"
                            />
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-gray-500 dark:text-gray-400">
                                    {{ $action['label'] }}
                                </p>
                                @if ($action['comingSoon'] ?? false)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                        Coming Soon
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-400 dark:text-gray-500">
                                {{ $action['description'] }}
                            </p>
                        </div>
                    </button>
                @endif
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
