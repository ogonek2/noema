<?php

namespace App\Filament\Pages;

use App\Services\BunnyStorageService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class CdnBrowser extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cloud';

    protected static string|\UnitEnum|null $navigationGroup = 'Медіа';

    protected static ?string $navigationLabel = 'Bunny CDN';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.cdn-browser';

    public string $currentPath = '';

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public function mount(): void
    {
        $this->refreshListing();
    }

    public function refreshListing(): void
    {
        $bunny = app(BunnyStorageService::class);
        $prefix = trim($this->currentPath, '/');
        $listPath = $prefix === '' ? '' : $prefix.'/';

        $this->items = collect($bunny->list($listPath))
            ->sortBy([
                fn (array $item) => ! ($item['IsDirectory'] ?? false),
                fn (array $item) => $item['ObjectName'] ?? '',
            ])
            ->values()
            ->all();
    }

    public function openDirectory(string $name): void
    {
        $this->currentPath = trim($this->currentPath.'/'.$name, '/');
        $this->refreshListing();
    }

    public function goUp(): void
    {
        if ($this->currentPath === '') {
            return;
        }

        $parts = explode('/', $this->currentPath);
        array_pop($parts);
        $this->currentPath = implode('/', $parts);
        $this->refreshListing();
    }

    public function deleteItem(string $name): void
    {
        $path = trim($this->currentPath.'/'.$name, '/');
        app(BunnyStorageService::class)->delete($path);
        $this->refreshListing();

        Notification::make()->title('Файл видалено')->success()->send();
    }

    public function publicUrl(string $name): string
    {
        $path = trim($this->currentPath.'/'.$name, '/');

        return app(BunnyStorageService::class)->publicUrl($path);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')
                ->label('Завантажити')
                ->form([
                    FileUpload::make('files')
                        ->label('Файли')
                        ->disk('bunny')
                        ->directory(fn () => trim($this->currentPath, '/') ?: 'uploads')
                        ->multiple()
                        ->required(),
                ])
                ->action(function (): void {
                    $this->refreshListing();
                    Notification::make()->title('Файли завантажено на Bunny CDN')->success()->send();
                }),
            Action::make('refresh')
                ->label('Оновити')
                ->action(fn () => $this->refreshListing()),
        ];
    }
}
