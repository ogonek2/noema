<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\Pages\Concerns\HasProductDetailTabs;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Catalog;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

class CreateProduct extends EditRecord
{
    use HasProductDetailTabs;

    private const DRAFT_SESSION_KEY = 'product_create_draft_id';

    protected static string $resource = ProductResource::class;

    public function mount(int|string $record = 0): void
    {
        unset($record);

        $draftId = session(self::DRAFT_SESSION_KEY);

        if ($draftId) {
            $draft = Product::query()->find($draftId);

            if ($draft) {
                $this->record = $draft;
                $this->authorizeAccess();
                $this->fillForm();
                $this->previousUrl = url()->previous();

                return;
            }

            session()->forget(self::DRAFT_SESSION_KEY);
        }

        $this->record = $this->createDraftProduct();
        session([self::DRAFT_SESSION_KEY => $this->record->getKey()]);

        $this->authorizeAccess();
        $this->fillForm();
        $this->previousUrl = url()->previous();
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canCreate(), 403);
    }

    protected function createDraftProduct(): Product
    {
        $catalogId = Catalog::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->value('id');

        if ($catalogId === null) {
            Notification::make()
                ->title('Спочатку створіть каталог')
                ->body('Без каталогу товар не можна додати.')
                ->danger()
                ->send();

            $this->redirect(static::getResource()::getUrl('index'));

            throw new Halt;
        }

        $token = strtolower((string) Str::ulid());

        /** @var Product $product */
        $product = static::getModel()::query()->create([
            'catalog_id' => $catalogId,
            'model_slug' => 'draft-'.$token,
            'name' => 'Новий товар',
            'slug' => 'draft-'.$token,
            'sku' => 'DRAFT-'.$token,
            'price' => 0,
            'is_active' => false,
            'is_featured' => false,
            'sort_order' => 0,
        ]);

        return $product;
    }

    public function getTitle(): string|Htmlable
    {
        return 'Створити товар';
    }

    public function getBreadcrumb(): string
    {
        return 'Створити';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Скасувати')
                ->modalHeading('Скасувати створення?')
                ->modalDescription('Чернетку товару буде видалено.')
                ->successRedirectUrl(static::getResource()::getUrl('index'))
                ->after(fn (): mixed => session()->forget(self::DRAFT_SESSION_KEY)),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Товар збережено';
    }

    protected function getRedirectUrl(): string
    {
        session()->forget(self::DRAFT_SESSION_KEY);

        return static::getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
