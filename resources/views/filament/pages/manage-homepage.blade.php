<x-filament-panels::page>
    @push('styles')
        <style>
            .fi-homepage-tabs.fi-sc-tabs.fi-vertical {
                width: 100%;
            }

            .fi-homepage-tabs.fi-sc-tabs.fi-vertical > .fi-tabs {
                flex: 0 0 13.5rem;
                min-width: 13.5rem;
            }

            .fi-homepage-tabs.fi-sc-tabs.fi-vertical > .fi-sc-tabs-tab.fi-active {
                flex: 1 1 auto;
                min-width: 0;
            }
        </style>
    @endpush



    <div class="w-full">
        {{ $this->form }}
    </div>
</x-filament-panels::page>
