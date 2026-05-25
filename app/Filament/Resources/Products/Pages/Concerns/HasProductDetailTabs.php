<?php

namespace App\Filament\Resources\Products\Pages\Concerns;

trait HasProductDetailTabs
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
