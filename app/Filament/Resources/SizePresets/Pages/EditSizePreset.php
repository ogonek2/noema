<?php

namespace App\Filament\Resources\SizePresets\Pages;

use App\Filament\Resources\SizePresets\Pages\Concerns\HasSizePresetDetailTabs;
use App\Filament\Resources\SizePresets\SizePresetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSizePreset extends EditRecord
{
    use HasSizePresetDetailTabs;

    protected static string $resource = SizePresetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
