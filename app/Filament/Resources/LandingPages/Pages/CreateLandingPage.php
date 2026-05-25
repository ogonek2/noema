<?php

namespace App\Filament\Resources\LandingPages\Pages;

use App\Filament\Resources\LandingPages\LandingPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLandingPage extends CreateRecord
{
    protected static string $resource = LandingPageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
