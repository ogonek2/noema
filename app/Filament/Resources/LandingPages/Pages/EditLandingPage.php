<?php

namespace App\Filament\Resources\LandingPages\Pages;

use App\Filament\Resources\LandingPages\LandingPageResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLandingPage extends EditRecord
{
    protected static string $resource = LandingPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Переглянути')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (): string => $this->getRecord()->publicUrl())
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->getRecord()->is_published),
            DeleteAction::make(),
        ];
    }
}
