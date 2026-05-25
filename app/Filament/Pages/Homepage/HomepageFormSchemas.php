<?php

namespace App\Filament\Pages\Homepage;

use App\Filament\Concerns\UsesBunnyUpload;
use App\Models\Product;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;

class HomepageFormSchemas
{
    use UsesBunnyUpload;

    /** @return \Closure(): array<string, string> */
    public static function productOptions(): \Closure
    {
        return fn () => Product::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public static function tabs(): Tabs
    {
        $products = self::productOptions();

        return Tabs::make('homepage')
            ->persistTabInQueryString('homepage_tab')
            ->vertical()
            ->extraAttributes([
                'class' => 'fi-homepage-tabs w-full',
            ])
            ->tabs([
                self::globalsTab($products),
                self::heroTab(),
                self::aboutTab(),
                self::productTab(),
                self::benefitsTab(),
                self::audienceTab(),
                self::reviewsTab(),
                self::ribbonTab(),
                self::statementTab(),
                self::footerTab(),
                self::navigatorTab(),
            ])
            ->columnSpanFull();
    }

    /** @param  \Closure(): array<string, string>  $products */
    private static function globalsTab(\Closure $products): Tab
    {
        return Tab::make('Загальне')
            ->icon('heroicon-o-cog-6-tooth')
            ->schema([
                Section::make('Глобальні налаштування')
                    ->description('Слайдер товарів у блоці «Продукт» та spotlight-товар для секцій, де увімкнено підстановку з каталогу.')
                    ->schema([
                        Select::make('spotlight_product_id')
                            ->label('Spotlight-товар')
                            ->helperText('Основний товар бренду: підставляється в блок «Продукт», «Переваги» та «Statement», якщо не задано власний текст.')
                            ->options($products)
                            ->searchable()
                            ->nullable(),
                        Select::make('featured_product_ids')
                            ->label('Товари в слайдері (блок «Продукт»)')
                            ->helperText('Порожньо = автоматично всі «Рекомендовані» з каталогу.')
                            ->options($products)
                            ->multiple()
                            ->searchable(),
                        Toggle::make('use_catalog_audience')
                            ->label('Блок «Для кого»: картки з каталогів')
                            ->helperText('Вимкніть, щоб використовувати власні картки з вкладки «Для кого».')
                            ->default(true),
                    ])
                    ->columns(1),
            ]);
    }

    private static function heroTab(): Tab
    {
        return Tab::make('Hero')
            ->icon('heroicon-o-sparkles')
            ->schema([
                Fieldset::make('Перший екран (#hero)')
                    ->statePath('hero')
                    ->schema([
                        TextInput::make('tagline')->label('Підзаголовок під логотипом')->columnSpanFull(),
                        self::bunnyUpload('hero_image', 'homepage/hero')->label('Фонове зображення (праворуч)'),
                        Textarea::make('side_link_label')->label('Текст бокового посилання')->rows(2),
                        TextInput::make('side_link_href')->label('URL бокового посилання')->placeholder('/catalog'),
                        TextInput::make('footer_tagline')->label('Рядок над соцмережами'),
                        TextInput::make('scroll_hint')->label('Підказка «вниз»'),
                        TextInput::make('instagram_url')->label('Instagram'),
                        TextInput::make('facebook_url')->label('Facebook'),
                        TextInput::make('tiktok_url')->label('TikTok'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    private static function aboutTab(): Tab
    {
        return Tab::make('Про бренд')
            ->icon('heroicon-o-building-storefront')
            ->schema([
                Fieldset::make('Секція #about-us')
                    ->statePath('about_us')
                    ->schema([
                        TextInput::make('badge')->label('Бейдж')->placeholder('[ NOEMA ]'),
                        TextInput::make('title_line1')->label('Заголовок · рядок 1'),
                        TextInput::make('title_line2')->label('Заголовок · рядок 2'),
                        Textarea::make('paragraph_1')->label('Абзац 1 (якщо немає каталогу)')->rows(3)->columnSpanFull(),
                        Textarea::make('paragraph_2')->label('Абзац 2')->rows(3)->columnSpanFull(),
                        Section::make('Кнопки')
                            ->schema([
                                TextInput::make('cta_primary')->label('Основна · текст'),
                                TextInput::make('cta_primary_href')
                                    ->label('Основна · посилання')
                                    ->placeholder('/catalog'),
                                TextInput::make('cta_secondary')->label('Друга · текст'),
                                TextInput::make('cta_secondary_href')
                                    ->label('Друга · посилання')
                                    ->placeholder('/catalog'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                        Textarea::make('footer_note')->label('Текст під кнопками')->rows(2)->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    private static function productTab(): Tab
    {
        return Tab::make('ПРОДУКТ')
            ->icon('heroicon-o-shopping-bag')
            ->badge('5')
            ->schema([
                Text::make('Текстова частина секції #product (під слайдером товарів). Слайдер і spotlight — вкладка «Загальне».')
                    ->columnSpanFull(),

                Fieldset::make('Секція #product — контент')
                    ->statePath('product_box')
                    ->schema([
                        Section::make('1. Заголовок «ПРОДУКТ»')
                            ->schema([
                                TextInput::make('title')->label('Великий заголовок')->placeholder('Продукт'),
                                TextInput::make('catalog_label')->label('Посилання праворуч')->placeholder('[ КАТАЛОГ ]'),
                                TextInput::make('catalog_href')->label('URL каталогу')->placeholder('/catalog'),
                                TextInput::make('made_with')->label('Підпис внизу справа')->placeholder('Made with Noema'),
                            ])
                            ->columns(2),

                        Section::make('2. Короткий опис')
                            ->description('Великий текст + ATHLETIC FIT')
                            ->schema([
                                Toggle::make('use_product_fallback')
                                    ->label('Якщо поле порожнє — брати текст з spotlight-товару')
                                    ->default(true)
                                    ->columnSpanFull(),
                                Textarea::make('headline')
                                    ->label('Основний текст')
                                    ->helperText('Напр.: «Хірургічний костюм Forge у кольорі Синій…»')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                TextInput::make('subtitle')
                                    ->label('Підпис (ATHLETIC FIT)')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('3. Чорна смуга з тегами')
                            ->schema([
                                Toggle::make('prepend_product_fabric_tag')
                                    ->label('Додати склад тканини з товару першим тегом')
                                    ->default(true)
                                    ->columnSpanFull(),
                                Repeater::make('fabric_tags')
                                    ->label('Теги на чорній смузі')
                                    ->schema([
                                        TextInput::make('label')->label('Текст')->required(),
                                    ])
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): string => $state['label'] ?? 'Тег')
                                    ->addActionLabel('Додати тег')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('4. Дві колонки')
                            ->schema([
                                Textarea::make('column_left_text')->label('Ліва колонка · текст')->rows(4),
                                TextInput::make('column_left_caption')->label('Ліва · підпис (ПРЯМИЙ ПРОФЕСІЙНИЙ КРІЙ)'),
                                Textarea::make('column_right_text')->label('Права колонка · текст')->rows(4),
                                TextInput::make('column_right_caption')->label('Права · підпис (склад тканини)'),
                            ])
                            ->columns(2),

                        Section::make('5. Кнопки')
                            ->schema([
                                TextInput::make('cta_primary_label')->label('Основна · текст')->placeholder('Обрати костюм'),
                                Toggle::make('cta_primary_link_product')
                                    ->label('Основна веде на сторінку spotlight-товару')
                                    ->default(true)
                                    ->columnSpanFull(),
                                TextInput::make('cta_primary_href')
                                    ->label('Основна · посилання')
                                    ->placeholder('/catalog')
                                    ->helperText('Якщо увімкнено spotlight — ігнорується. Інакше порожньо = каталог.'),
                                TextInput::make('cta_secondary_label')->label('Друга · текст')->placeholder('Каталог'),
                                TextInput::make('cta_secondary_href')
                                    ->label('Друга · посилання')
                                    ->placeholder('/catalog')
                                    ->helperText('Порожньо = каталог.'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function benefitsTab(): Tab
    {
        return Tab::make('Переваги')
            ->icon('heroicon-o-check-badge')
            ->schema([
                Fieldset::make('Секція #benefits')
                    ->statePath('benefits')
                    ->schema([
                        TextInput::make('title_line1')->label('Заголовок · рядок 1'),
                        TextInput::make('title_line2')->label('Заголовок · рядок 2'),
                        TextInput::make('badge')->label('Бейдж'),
                        Textarea::make('description_fallback')->label('Абзац (fallback)')->rows(3)->columnSpanFull(),
                        TextInput::make('made_with')->label('Made with'),
                        self::bunnyUpload('fallback_image', 'homepage/benefits')
                            ->label('Фото праворуч')
                            ->helperText('Має пріоритет над фото spotlight-товару. Якщо порожньо — показується товар з вкладки «Загальне».'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Список переваг (6 карток)')
                    ->schema([
                        Repeater::make('benefits_list')
                            ->label('')
                            ->schema([
                                Hidden::make('id'),
                                TextInput::make('number_label')->label('№')->maxLength(16),
                                TextInput::make('title')->label('Заголовок')->required(),
                                TextInput::make('text')->label('Підпис'),
                                TextInput::make('sort_order')->label('Сорт.')->numeric()->default(0),
                                Toggle::make('is_active')->label('Активна')->default(true),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): string => trim(($state['number_label'] ?? '').' '.($state['title'] ?? 'Перевага')))
                            ->addActionLabel('Додати перевагу')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function audienceTab(): Tab
    {
        return Tab::make('Для кого')
            ->icon('heroicon-o-user-group')
            ->schema([
                Section::make('Картки #audience')
                    ->description('Працює лише якщо у «Загальне» вимкнено «картки з каталогів».')
                    ->schema([
                        Repeater::make('audience_cards')
                            ->label('')
                            ->schema([
                                Hidden::make('id'),
                                TextInput::make('name')->label('Назва')->required(),
                                self::bunnyUpload('image_path', 'homepage/audience')->label('Зображення'),
                                TextInput::make('href')->label('Посилання'),
                                TextInput::make('sort_order')->label('Сорт.')->numeric()->default(0),
                                Toggle::make('is_active')->label('Активна')->default(true),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): string => $state['name'] ?? 'Картка')
                            ->addActionLabel('Додати картку')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function reviewsTab(): Tab
    {
        return Tab::make('Відгуки')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->schema([
                Section::make('Карусель #reviews')
                    ->schema([
                        Repeater::make('reviews')
                            ->label('')
                            ->schema([
                                Hidden::make('id'),
                                Textarea::make('quote')->label('Відгук')->required()->rows(3)->columnSpanFull(),
                                TextInput::make('author_name')->label("Ім'я")->required(),
                                TextInput::make('author_role')->label('Посада / роль'),
                                TextInput::make('sort_order')->label('Сорт.')->numeric()->default(0),
                                Toggle::make('is_active')->label('Активний')->default(true),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): string => $state['author_name'] ?? 'Відгук')
                            ->addActionLabel('Додати відгук')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function ribbonTab(): Tab
    {
        return Tab::make('Ribbon')
            ->icon('heroicon-o-photo')
            ->schema([
                Section::make('Галерея #ribbon')
                    ->description('Горизонтальна стрічка зображень. Порожньо → фото з товарів або папки gallery.')
                    ->schema([
                        Repeater::make('ribbon_images')
                            ->label('')
                            ->schema([
                                Hidden::make('id'),
                                self::bunnyUpload('path', 'homepage/ribbon')->label('Зображення')->required(),
                                TextInput::make('alt_text')->label('Alt'),
                                TextInput::make('width')->label('Ширина')->numeric()->default(900),
                                TextInput::make('height')->label('Висота')->numeric()->default(1200),
                                TextInput::make('sort_order')->label('Сорт.')->numeric()->default(0),
                                Toggle::make('is_active')->label('Активне')->default(true),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->addActionLabel('Додати фото')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function statementTab(): Tab
    {
        return Tab::make('Statement')
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->schema([
                Fieldset::make('Секція #statement')
                    ->statePath('statement')
                    ->schema([
                        TextInput::make('brand_title')->label('Заголовок'),
                        Textarea::make('quote_fallback')->label('Цитата (fallback)')->rows(4)->columnSpanFull(),
                        TextInput::make('made_with')->label('Made with'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    private static function footerTab(): Tab
    {
        return Tab::make('Футер')
            ->icon('heroicon-o-document-text')
            ->schema([
                Fieldset::make('Секція #site-footer')
                    ->statePath('footer')
                    ->schema([
                        Section::make('Колонка бренду')
                            ->schema([
                                Textarea::make('description')->label('Опис')->rows(3)->columnSpanFull(),
                                TextInput::make('cta_primary')->label('Кнопка 1 · текст'),
                                TextInput::make('cta_primary_href')
                                    ->label('Кнопка 1 · посилання')
                                    ->placeholder('/catalog'),
                                TextInput::make('cta_secondary')->label('Кнопка 2 · текст'),
                                TextInput::make('cta_secondary_href')
                                    ->label('Кнопка 2 · посилання')
                                    ->placeholder('/catalog'),
                                TextInput::make('made_with')->label('Made with'),
                            ])
                            ->columns(2),

                        Section::make('Контакти')
                            ->schema([
                                TextInput::make('phone_1')->label('Телефон 1'),
                                TextInput::make('phone_2')->label('Телефон 2'),
                                TextInput::make('email')->label('Email'),
                                TextInput::make('office_title')->label('Офіс · заголовок'),
                                Textarea::make('office_address')->label('Офіс · адреса')->rows(2),
                                TextInput::make('partners_title')->label('Партнери · заголовок'),
                                Textarea::make('partners_address')->label('Партнери · адреса')->rows(2),
                                TextInput::make('copyright')->label('Копірайт'),
                            ])
                            ->columns(2),

                        Section::make('Посилання')
                            ->schema([
                                Repeater::make('legal_links')
                                    ->label('Юридичні')
                                    ->schema([
                                        TextInput::make('label')->label('Текст')->required(),
                                        TextInput::make('href')->label('URL')->required(),
                                    ])
                                    ->columns(2)
                                    ->addActionLabel('Додати')
                                    ->columnSpanFull(),
                                Repeater::make('navigator_links')
                                    ->label('Навігатор у футері')
                                    ->schema([
                                        TextInput::make('label')->label('Текст')->required(),
                                        TextInput::make('href')->label('URL')->required(),
                                    ])
                                    ->columns(2)
                                    ->addActionLabel('Додати')
                                    ->columnSpanFull(),
                            ])
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function navigatorTab(): Tab
    {
        return Tab::make('Навігація')
            ->icon('heroicon-o-bars-3')
            ->schema([
                Fieldset::make('Меню в шапці')
                    ->statePath('navigator')
                    ->schema([
                        Repeater::make('links')
                            ->label('Пункти меню')
                            ->schema([
                                TextInput::make('label')->label('Текст')->required(),
                                TextInput::make('href')->label('URL')->required(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Додати пункт')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
