<?php

namespace App\Filament\Concerns;

use Filament\Forms\Components\FileUpload;

trait UsesBunnyUpload
{
    protected static function bunnyUpload(string $name, string $directory = 'media'): FileUpload
    {
        return FileUpload::make($name)
            ->disk('bunny')
            ->visibility('public')
            ->directory($directory)
            ->image()
            ->imageEditor()
            ->downloadable()
            ->openable();
    }
}
