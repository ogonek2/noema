<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Поточна папка</x-slot>
            <div class="flex flex-wrap items-center gap-2 text-sm">
                <x-filament::button size="sm" color="gray" wire:click="goUp" :disabled="$currentPath === ''">
                    На рівень вище
                </x-filament::button>
                <span class="font-mono text-gray-600">/{{ $currentPath ?: '' }}</span>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Файли на CDN</x-slot>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @forelse ($items as $item)
                    @php
                        $name = $item['ObjectName'] ?? '';
                        $isDir = (bool) ($item['IsDirectory'] ?? false);
                        $isImage = ! $isDir && preg_match('/\.(jpe?g|png|webp|gif|svg)$/i', $name);
                    @endphp
                    <div class="rounded-xl border border-gray-200 p-3 dark:border-gray-700">
                        @if ($isDir)
                            <button type="button" wire:click="openDirectory('{{ $name }}')"
                                class="flex w-full items-center gap-2 text-left text-sm font-medium">
                                <x-heroicon-o-folder class="h-5 w-5" />
                                {{ $name }}
                            </button>
                        @else
                            @if ($isImage)
                                <img src="{{ $this->publicUrl($name) }}" alt="{{ $name }}"
                                    class="mb-2 aspect-square w-full rounded-lg object-cover">
                            @endif
                            <p class="truncate text-xs font-mono" title="{{ $name }}">{{ $name }}</p>
                            <div class="mt-2 flex gap-2">
                                <a href="{{ $this->publicUrl($name) }}" target="_blank"
                                    class="text-xs text-primary-600 underline">Відкрити</a>
                                <button type="button" wire:click="deleteItem('{{ $name }}')"
                                    class="text-xs text-danger-600">Видалити</button>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Папка порожня</p>
                @endforelse
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
