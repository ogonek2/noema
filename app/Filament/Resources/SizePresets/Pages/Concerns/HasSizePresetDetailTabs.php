<?php

namespace App\Filament\Resources\SizePresets\Pages\Concerns;

trait HasSizePresetDetailTabs
{
    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Основне';
    }
}
