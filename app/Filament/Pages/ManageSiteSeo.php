<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesBunnyUpload;
use App\Models\SiteSeoSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ManageSiteSeo extends Page implements HasForms
{
    use InteractsWithForms;
    use UsesBunnyUpload;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static string|\UnitEnum|null $navigationGroup = 'Система';

    protected static ?string $navigationLabel = 'SEO та OG';

    protected static ?int $navigationSort = 80;

    protected static ?string $title = 'SEO, Open Graph та іконки';

    protected string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSeoSettings::current()->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Сайт')
                    ->schema([
                        TextInput::make('site_name')->label('Назва сайту')->required()->maxLength(120),
                        TextInput::make('title_separator')
                            ->label('Роздільник у title')
                            ->default(' | ')
                            ->maxLength(8)
                            ->helperText('Наприклад: «Каталог | NOEMA»'),
                        TextInput::make('robots')
                            ->label('Robots (за замовчуванням)')
                            ->default('index, follow')
                            ->helperText('noindex, nofollow — для службових сторінок окремо'),
                    ])
                    ->columns(3),

                Section::make('Головна сторінка')
                    ->schema([
                        TextInput::make('home_meta_title')->label('Title')->columnSpanFull(),
                        Textarea::make('home_meta_description')->label('Meta description')->rows(3)->columnSpanFull(),
                        Textarea::make('home_meta_keywords')
                            ->label('Ключові слова')
                            ->rows(2)
                            ->helperText('Через кому: NOEMA, медичний одяг, …')
                            ->columnSpanFull(),
                    ]),

                Section::make('Каталог (список)')
                    ->schema([
                        TextInput::make('catalog_index_meta_title')->label('Title'),
                        Textarea::make('catalog_index_meta_description')->label('Meta description')->rows(2)->columnSpanFull(),
                        Textarea::make('catalog_index_meta_keywords')->label('Ключові слова')->rows(2)->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('За замовчуванням (товари, лендінги без своїх полів)')
                    ->schema([
                        Textarea::make('default_meta_description')->label('Meta description')->rows(2)->columnSpanFull(),
                        Textarea::make('default_meta_keywords')->label('Ключові слова')->rows(2)->columnSpanFull(),
                    ]),

                Section::make('Open Graph (соцмережі)')
                    ->description('Використовується, якщо сторінка не має власного OG-зображення.')
                    ->schema([
                        TextInput::make('og_site_name')->label('og:site_name'),
                        TextInput::make('og_locale')->label('og:locale')->default('uk_UA'),
                        TextInput::make('og_default_title')->label('og:title (за замовч.)')->columnSpanFull(),
                        Textarea::make('og_default_description')->label('og:description (за замовч.)')->rows(2)->columnSpanFull(),
                        static::bunnyUpload('og_default_image', 'seo')
                            ->label('OG-зображення за замовчуванням (1200×630)')
                            ->helperText('Рекомендовано 1200×630 px для Facebook / Telegram.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Іконки сайту')
                    ->schema([
                        static::bunnyUpload('favicon_path', 'seo')
                            ->label('Favicon (PNG, 32×32 або 64×64)')
                            ->columnSpanFull(),
                        static::bunnyUpload('apple_touch_icon_path', 'seo')
                            ->label('Apple Touch Icon (180×180)')
                            ->columnSpanFull(),
                    ]),

                Section::make('Додатково')
                    ->schema([
                        TextInput::make('twitter_site')
                            ->label('Twitter @username')
                            ->placeholder('@noema'),
                        TextInput::make('google_site_verification')
                            ->label('Google Search Console verification'),
                        Textarea::make('notes')->label('Примітки')->rows(2)->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Зберегти')
                ->action(function (): void {
                    SiteSeoSettings::current()->fill($this->data)->save();

                    Notification::make()->title('SEO-налаштування збережено')->success()->send();
                }),
        ];
    }
}
