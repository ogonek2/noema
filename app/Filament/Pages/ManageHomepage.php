<?php

namespace App\Filament\Pages;

use App\Enums\HomepageBlockSlug;
use App\Filament\Pages\Homepage\HomepageFormSchemas;
use App\Models\HomepageAudienceCard;
use App\Models\HomepageBenefit;
use App\Models\HomepageReview;
use App\Models\HomepageRibbonImage;
use App\Services\HomepageContentService;
use App\Support\MediaUrl;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;

class ManageHomepage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home-modern';

    protected static string|\UnitEnum|null $navigationGroup = 'Контент';

    protected static ?string $navigationLabel = 'Головна сторінка';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Контент головної сторінки';

    protected Width|string|null $maxContentWidth = Width::Full;

    protected string $view = 'filament.pages.manage-homepage';

    public ?array $data = [];

    public function mount(): void
    {
        $homepage = app(HomepageContentService::class);

        if (! $homepage->isInstalled()) {
            Notification::make()
                ->title('Таблиці контенту не знайдено')
                ->body('Запустіть: php artisan migrate --force')
                ->danger()
                ->send();

            return;
        }

        $homepage->ensureDefaults();
        $payload = $homepage->adminPayload();

        $this->form->fill([
            'spotlight_product_id' => $payload['globals']['spotlight_product_id'],
            'featured_product_ids' => $payload['globals']['featured_product_ids'] ?? [],
            'use_catalog_audience' => $payload['globals']['use_catalog_audience'],
            'hero' => $homepage->blockContent(HomepageBlockSlug::Hero),
            'about_us' => $homepage->blockContent(HomepageBlockSlug::AboutUs),
            'product_box' => $homepage->blockContent(HomepageBlockSlug::ProductBox),
            'benefits' => $homepage->blockContent(HomepageBlockSlug::Benefits),
            'statement' => $homepage->blockContent(HomepageBlockSlug::Statement),
            'footer' => $homepage->blockContent(HomepageBlockSlug::Footer),
            'navigator' => $homepage->blockContent(HomepageBlockSlug::Navigator),
            'benefits_list' => $payload['benefits']->map(fn ($item) => [
                'id' => $item->id,
                'number_label' => $item->number_label,
                'title' => $item->title,
                'text' => $item->text,
                'sort_order' => $item->sort_order,
                'is_active' => $item->is_active,
            ])->all(),
            'audience_cards' => $payload['audience_cards']->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'image_path' => $item->image_path,
                'href' => $item->href,
                'sort_order' => $item->sort_order,
                'is_active' => $item->is_active,
            ])->all(),
            'reviews' => $payload['reviews']->map(fn ($item) => [
                'id' => $item->id,
                'quote' => $item->quote,
                'author_name' => $item->author_name,
                'author_role' => $item->author_role,
                'sort_order' => $item->sort_order,
                'is_active' => $item->is_active,
            ])->all(),
            'ribbon_images' => $payload['ribbon_images']->map(fn ($item) => [
                'id' => $item->id,
                'path' => $item->path,
                'alt_text' => $item->alt_text,
                'width' => $item->width,
                'height' => $item->height,
                'sort_order' => $item->sort_order,
                'is_active' => $item->is_active,
            ])->all(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                HomepageFormSchemas::tabs(),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Зберегти все')
                ->icon('heroicon-o-check')
                ->action('save'),
            Action::make('preview')
                ->label('Переглянути сайт')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(route('home'))
                ->openUrlInNewTab(),
        ];
    }

    public function save(): void
    {
        $homepage = app(HomepageContentService::class);

        if (! $homepage->isInstalled()) {
            Notification::make()->title('Спочатку виконайте міграції')->danger()->send();

            return;
        }

        $data = $this->form->getState();

        $homepage->updateGlobals([
            'spotlight_product_id' => $data['spotlight_product_id'] ?? null,
            'featured_product_ids' => array_values($data['featured_product_ids'] ?? []),
            'use_catalog_audience' => (bool) ($data['use_catalog_audience'] ?? true),
        ]);

        $homepage->updateBlock(HomepageBlockSlug::Hero, $this->blockForSave(HomepageBlockSlug::Hero, $data['hero'] ?? []));
        $homepage->updateBlock(HomepageBlockSlug::AboutUs, $data['about_us'] ?? []);
        $homepage->updateBlock(HomepageBlockSlug::ProductBox, $data['product_box'] ?? []);
        $homepage->updateBlock(HomepageBlockSlug::Benefits, $this->blockForSave(HomepageBlockSlug::Benefits, $data['benefits'] ?? []));
        $homepage->updateBlock(HomepageBlockSlug::Statement, $data['statement'] ?? []);
        $homepage->updateBlock(HomepageBlockSlug::Footer, $data['footer'] ?? []);
        $homepage->updateBlock(HomepageBlockSlug::Navigator, $data['navigator'] ?? []);

        $this->syncBenefits($data['benefits_list'] ?? []);
        $this->syncAudienceCards($data['audience_cards'] ?? []);
        $this->syncReviews($data['reviews'] ?? []);
        $this->syncRibbonImages($data['ribbon_images'] ?? []);

        Notification::make()->title('Контент головної збережено')->success()->send();
    }

    /** @param  array<string, mixed>  $incoming */
    private function blockForSave(HomepageBlockSlug $slug, array $incoming): array
    {
        $existing = app(HomepageContentService::class)->blockContent($slug);

        foreach (['hero_image', 'fallback_image'] as $key) {
            if (! array_key_exists($key, $incoming)) {
                continue;
            }

            $normalized = MediaUrl::normalizePath($incoming[$key]);

            $incoming[$key] = filled($normalized)
                ? $normalized
                : ($existing[$key] ?? null);
        }

        return $incoming;
    }

    /** @param  list<array<string, mixed>>  $blocks */
    private function blockContent(array $blocks, string $slug): array
    {
        $block = collect($blocks)->firstWhere('slug', $slug);

        return is_array($block['content'] ?? null) ? $block['content'] : [];
    }

    /** @param  list<array<string, mixed>>  $items */
    private function syncBenefits(array $items): void
    {
        $this->syncModels(HomepageBenefit::class, $items, [
            'number_label', 'title', 'text', 'sort_order', 'is_active',
        ]);
    }

    /** @param  list<array<string, mixed>>  $items */
    private function syncAudienceCards(array $items): void
    {
        $existingById = HomepageAudienceCard::query()->pluck('image_path', 'id');

        $items = array_map(function (array $item) use ($existingById): array {
            $normalized = MediaUrl::normalizePath($item['image_path'] ?? null);
            $item['image_path'] = filled($normalized)
                ? $normalized
                : ($item['id'] ? $existingById[$item['id']] ?? null : null);

            return $item;
        }, $items);

        $this->syncModels(HomepageAudienceCard::class, $items, [
            'name', 'image_path', 'href', 'sort_order', 'is_active',
        ]);
    }

    /** @param  list<array<string, mixed>>  $items */
    private function syncReviews(array $items): void
    {
        $this->syncModels(HomepageReview::class, $items, [
            'quote', 'author_name', 'author_role', 'sort_order', 'is_active',
        ]);
    }

    /** @param  list<array<string, mixed>>  $items */
    private function syncRibbonImages(array $items): void
    {
        $existingById = HomepageRibbonImage::query()->pluck('path', 'id');

        $items = array_map(function (array $item) use ($existingById): array {
            $normalized = MediaUrl::normalizePath($item['path'] ?? null);
            $item['path'] = filled($normalized)
                ? $normalized
                : ($item['id'] ? $existingById[$item['id']] ?? null : null);

            return $item;
        }, $items);

        $this->syncModels(HomepageRibbonImage::class, $items, [
            'path', 'alt_text', 'width', 'height', 'sort_order', 'is_active',
        ]);
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     * @param  list<array<string, mixed>>  $items
     * @param  list<string>  $fields
     */
    private function syncModels(string $modelClass, array $items, array $fields): void
    {
        $keptIds = [];

        foreach ($items as $item) {
            $payload = collect($item)->only($fields)->all();
            $id = $item['id'] ?? null;

            if ($id) {
                $modelClass::query()->whereKey($id)->update($payload);
                $keptIds[] = (int) $id;

                continue;
            }

            $created = $modelClass::query()->create($payload);
            $keptIds[] = $created->id;
        }

        if ($keptIds === []) {
            $modelClass::query()->delete();

            return;
        }

        $modelClass::query()->whereNotIn('id', $keptIds)->delete();
    }
}
