<?php

namespace App\Filament\Resources\LandingPages\Schemas;

use App\Enums\LandingFormFieldType;
use App\Enums\LandingHeroImageFocal;
use App\Enums\LandingHeroImageMode;
use App\Enums\LandingSectionType;
use App\Filament\Concerns\UsesBunnyUpload;
use App\Models\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;

class LandingSectionForm
{
    use UsesBunnyUpload;

    /** @return list<\Filament\Forms\Components\Component|\Filament\Schemas\Components\Component> */
    public static function schema(): array
    {
        return [
            Section::make('Параметри секції')
                ->schema([
                    Grid::make(12)->schema([
                        Select::make('type')
                            ->label('Тип секції')
                            ->options(LandingSectionType::options())
                            ->required()
                            ->live()
                            ->native(false)
                            ->columnSpan(['default' => 12, 'lg' => 5]),
                        TextInput::make('admin_label')
                            ->label('Назва в адмінці')
                            ->placeholder('Напр.: Hero головний')
                            ->columnSpan(['default' => 12, 'lg' => 7]),
                        Toggle::make('is_active')
                            ->label('Активна на сайті')
                            ->default(true)
                            ->columnSpan(['default' => 6, 'lg' => 4]),
                        TextInput::make('sort_order')
                            ->label('Порядок')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(['default' => 6, 'lg' => 3]),
                    ]),
                ])
                ->columnSpanFull(),

            ...self::contentFieldsets(),
        ];
    }

    private static function richEditor(string $name, string $label): RichEditor
    {
        return RichEditor::make($name)
            ->label($label)
            ->columnSpanFull()
            ->toolbarButtons([
                ['bold', 'italic', 'underline', 'strike', 'link'],
                ['h2', 'h3'],
                ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                ['table'],
                ['attachFiles'],
                ['undo', 'redo'],
            ]);
    }

    /** @return list<Fieldset> */
    private static function contentFieldsets(): array
    {
        $is = fn (LandingSectionType $type): \Closure => fn (Get $get): bool => $get('type') === $type->value;

        return [
            Fieldset::make('Hero')
                ->statePath('content')
                ->visible($is(LandingSectionType::Hero))
                ->columnSpanFull()
                ->schema([
                    Section::make('Текст і навігація')
                        ->schema([
                            Select::make('nav_theme')
                                ->label('Тема навігації')
                                ->options(['dark' => 'Темна', 'light' => 'Світла'])
                                ->default('dark')
                                ->native(false)
                                ->columnSpanFull(),
                            TextInput::make('badge')->label('Бейдж')->columnSpanFull(),
                            TextInput::make('title')->label('Заголовок (рядки через \\)')->columnSpanFull(),
                            Textarea::make('subtitle')->label('Підзаголовок')->rows(3)->columnSpanFull(),
                        ])
                        ->columns(1)
                        ->columnSpanFull(),

                    Section::make('Зображення')
                        ->schema([
                            self::bunnyUpload('image', 'landings')->label('Файл зображення')->columnSpanFull(),
                            Select::make('image_mode')
                                ->label('Режим відображення')
                                ->options(LandingHeroImageMode::options())
                                ->default(LandingHeroImageMode::ColumnCover->value)
                                ->native(false)
                                ->live()
                                ->columnSpanFull(),
                            Text::make(fn (Get $get): string => LandingHeroImageMode::tryFrom($get('image_mode'))?->description() ?? '')
                                ->columnSpanFull(),
                            Select::make('image_focal')
                                ->label('Фокус кадру (object-position)')
                                ->options(LandingHeroImageFocal::options())
                                ->default(LandingHeroImageFocal::Top->value)
                                ->native(false)
                                ->visible(fn (Get $get): bool => $get('image_mode') !== LandingHeroImageMode::FreeObject->value)
                                ->columnSpanFull(),
                            Section::make('Позиція та розмір (вільний об’єкт)')
                                ->description('Координати в % від секції. Центр фото — у точці X/Y.')
                                ->visible(fn (Get $get): bool => $get('image_mode') === LandingHeroImageMode::FreeObject->value)
                                ->schema([
                                    TextInput::make('img_pos_x')->label('X (%)')->numeric()->default(72)->minValue(0)->maxValue(100),
                                    TextInput::make('img_pos_y')->label('Y (%)')->numeric()->default(50)->minValue(0)->maxValue(100),
                                    TextInput::make('img_width')->label('Ширина (%)')->numeric()->default(42)->minValue(8)->maxValue(100),
                                    TextInput::make('img_height')->label('Висота (%)')->numeric()->default(78)->minValue(8)->maxValue(100),
                                    TextInput::make('img_opacity')->label('Прозорість (%)')->numeric()->default(100)->minValue(0)->maxValue(100),
                                    Select::make('img_fit')
                                        ->label('Вписування')
                                        ->options([
                                            'cover' => 'Заливка (cover)',
                                            'contain' => 'Контейнер (contain)',
                                            'fill' => 'Розтягнути (fill)',
                                        ])
                                        ->default('cover')
                                        ->native(false),
                                ])
                                ->columns(2)
                                ->columnSpanFull(),
                        ])
                        ->columnSpanFull(),

                    Section::make('Кнопки')
                        ->schema([
                            TextInput::make('cta_primary_label')->label('Основна · текст'),
                            TextInput::make('cta_primary_href')->label('Основна · URL')->placeholder('/catalog'),
                            TextInput::make('cta_secondary_label')->label('Друга · текст'),
                            TextInput::make('cta_secondary_href')->label('Друга · URL')->placeholder('/catalog'),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ]),

            Fieldset::make('Текст + зображення')
                ->statePath('content')
                ->visible($is(LandingSectionType::Split))
                ->columnSpanFull()
                ->schema([
                    Select::make('nav_theme')->label('Тема навігації')->options(['light' => 'Світла', 'dark' => 'Темна'])->default('light'),
                    Select::make('layout')->label('Розташування')->options([
                        'image_left' => 'Зображення зліва',
                        'image_right' => 'Зображення справа',
                    ])->default('image_right'),
                    TextInput::make('badge')->label('Бейдж')->columnSpan(['default' => 12, 'lg' => 4]),
                    TextInput::make('title')->label('Заголовок')->columnSpan(['default' => 12, 'lg' => 8]),
                    self::richEditor('body', 'Текст'),
                    self::bunnyUpload('image', 'landings')->label('Зображення')->columnSpanFull(),
                    TextInput::make('cta_label')->label('Кнопка · текст'),
                    TextInput::make('cta_href')->label('Кнопка · посилання')->placeholder('/catalog'),
                ])
                ->columns(2),

            Fieldset::make('Текстовий блок')
                ->statePath('content')
                ->visible($is(LandingSectionType::Content))
                ->columnSpanFull()
                ->schema([
                    Grid::make(12)->schema([
                        Select::make('nav_theme')->label('Тема навігації')->options(['light' => 'Світла', 'dark' => 'Темна'])->default('light')->columnSpan(4),
                        Select::make('align')->label('Вирівнювання')->options([
                            'center' => 'По центру',
                            'left' => 'Ліворуч',
                        ])->default('center')->columnSpan(4),
                        Select::make('prose_width')->label('Ширина тексту')->options([
                            'default' => 'Стандарт',
                            'wide' => 'Широка',
                            'full' => 'На всю ширину',
                        ])->default('default')->columnSpan(4),
                        TextInput::make('badge')->label('Бейдж')->columnSpan(['default' => 12, 'lg' => 4]),
                        TextInput::make('title')->label('Заголовок')->columnSpan(['default' => 12, 'lg' => 8]),
                    ]),
                    self::richEditor('body', 'Текст'),
                ]),

            Fieldset::make('Переваги')
                ->statePath('content')
                ->visible($is(LandingSectionType::Features))
                ->columnSpanFull()
                ->schema([
                    Select::make('nav_theme')->label('Тема навігації')->options(['light' => 'Світла', 'dark' => 'Темна'])->default('light'),
                    TextInput::make('title')->label('Заголовок'),
                    Textarea::make('subtitle')->label('Підзаголовок')->rows(2)->columnSpanFull(),
                    Select::make('columns')->label('Колонки')->options(['2' => '2', '3' => '3', '4' => '4'])->default('3'),
                    Repeater::make('items')
                        ->label('Картки')
                        ->schema([
                            TextInput::make('title')->label('Заголовок')->required(),
                            Textarea::make('text')->label('Текст')->rows(3)->required(),
                            self::bunnyUpload('image', 'landings')->label('Зображення'),
                        ])
                        ->defaultItems(0)
                        ->collapsible()
                        ->itemLabel(fn (array $state): string => $state['title'] ?? 'Картка')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Fieldset::make('CTA банер')
                ->statePath('content')
                ->visible($is(LandingSectionType::Cta))
                ->columnSpanFull()
                ->schema([
                    Select::make('style')->label('Стиль фону')->options([
                        'dark' => 'Темний',
                        'light' => 'Світлий',
                        'blue' => 'Синій (бренд)',
                    ])->default('dark'),
                    TextInput::make('title')->label('Заголовок')->columnSpanFull(),
                    Textarea::make('text')->label('Текст')->rows(3)->columnSpanFull(),
                    TextInput::make('cta_primary_label')->label('Кнопка 1 · текст'),
                    TextInput::make('cta_primary_href')->label('Кнопка 1 · посилання'),
                    TextInput::make('cta_secondary_label')->label('Кнопка 2 · текст'),
                    TextInput::make('cta_secondary_href')->label('Кнопка 2 · посилання'),
                ])
                ->columns(2),

            Fieldset::make('Статистика')
                ->statePath('content')
                ->visible($is(LandingSectionType::Stats))
                ->columnSpanFull()
                ->schema([
                    Select::make('nav_theme')->label('Тема навігації')->options(['light' => 'Світла', 'dark' => 'Темна'])->default('light'),
                    Repeater::make('items')
                        ->label('Показники')
                        ->schema([
                            TextInput::make('value')->label('Значення')->required(),
                            TextInput::make('label')->label('Підпис')->required(),
                        ])
                        ->defaultItems(0)
                        ->collapsible()
                        ->itemLabel(fn (array $state): string => $state['value'] ?? 'Показник')
                        ->columnSpanFull(),
                ]),

            Fieldset::make('FAQ')
                ->statePath('content')
                ->visible($is(LandingSectionType::Faq))
                ->columnSpanFull()
                ->schema([
                    Select::make('nav_theme')->label('Тема навігації')->options(['light' => 'Світла', 'dark' => 'Темна'])->default('light'),
                    TextInput::make('title')->label('Заголовок секції')->columnSpanFull(),
                    Repeater::make('items')
                        ->label('Питання')
                        ->schema([
                            TextInput::make('question')->label('Питання')->required(),
                            self::richEditor('answer', 'Відповідь'),
                        ])
                        ->defaultItems(0)
                        ->collapsible()
                        ->itemLabel(fn (array $state): string => $state['question'] ?? 'Питання')
                        ->columnSpanFull(),
                ]),

            Fieldset::make('Галерея')
                ->statePath('content')
                ->visible($is(LandingSectionType::Gallery))
                ->columnSpanFull()
                ->schema([
                    Select::make('nav_theme')->label('Тема навігації')->options(['light' => 'Світла', 'dark' => 'Темна'])->default('light'),
                    TextInput::make('title')->label('Заголовок'),
                    Select::make('columns')->label('Колонки')->options(['2' => '2', '3' => '3', '4' => '4'])->default('3'),
                    Repeater::make('images')
                        ->label('Зображення')
                        ->schema([
                            self::bunnyUpload('path', 'landings')->label('Фото')->required(),
                            TextInput::make('alt')->label('Alt'),
                            TextInput::make('caption')->label('Підпис'),
                        ])
                        ->defaultItems(0)
                        ->collapsible()
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Fieldset::make('Товари')
                ->statePath('content')
                ->visible($is(LandingSectionType::Products))
                ->columnSpanFull()
                ->schema([
                    Select::make('nav_theme')->label('Тема навігації')->options(['light' => 'Світла', 'dark' => 'Темна'])->default('light'),
                    TextInput::make('title')->label('Заголовок'),
                    Textarea::make('subtitle')->label('Підзаголовок')->rows(2)->columnSpanFull(),
                    Select::make('product_ids')
                        ->label('Товари')
                        ->options(fn () => Product::query()->active()->orderBy('name')->pluck('name', 'id'))
                        ->multiple()
                        ->searchable()
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Fieldset::make('HTML')
                ->statePath('content')
                ->visible($is(LandingSectionType::RichHtml))
                ->columnSpanFull()
                ->schema([
                    Grid::make(12)->schema([
                        Select::make('nav_theme')->label('Тема навігації')->options(['light' => 'Світла', 'dark' => 'Темна'])->default('light')->columnSpan(6),
                        Select::make('prose_width')->label('Ширина контенту')->options([
                            'default' => 'Стандарт',
                            'wide' => 'Широка',
                            'full' => 'На всю ширину',
                        ])->default('wide')->columnSpan(6),
                    ]),
                    self::richEditor('html', 'Контент'),
                ]),

            Fieldset::make('Відступ')
                ->statePath('content')
                ->visible($is(LandingSectionType::Spacer))
                ->columnSpanFull()
                ->schema([
                    Select::make('size')->label('Висота')->options([
                        'sm' => 'Малий',
                        'md' => 'Середній',
                        'lg' => 'Великий',
                        'xl' => 'Дуже великий',
                    ])->default('md'),
                ]),

            Fieldset::make('Форма')
                ->statePath('content')
                ->visible($is(LandingSectionType::Form))
                ->columnSpanFull()
                ->schema([
                    Section::make('Загальне')
                        ->schema([
                            TextInput::make('form_key')
                                ->label('Ключ форми')
                                ->helperText('Генерується при збереженні. Скопіюйте для перевірки endpoint.')
                                ->readOnly()
                                ->copyable()
                                ->dehydrated()
                                ->placeholder('Збережіть секцію — ключ зʼявиться тут')
                                ->columnSpanFull(),
                            Select::make('nav_theme')
                                ->label('Тема секції')
                                ->options(['light' => 'Світла', 'dark' => 'Темна'])
                                ->default('light')
                                ->native(false),
                            TextInput::make('title')->label('Заголовок форми')->columnSpanFull(),
                            Textarea::make('subtitle')->label('Підзаголовок')->rows(2)->columnSpanFull(),
                            TextInput::make('submit_label')->label('Текст кнопки')->default('Надіслати'),
                            Textarea::make('success_message')
                                ->label('Повідомлення після відправки')
                                ->default('Дякуємо! Ми звʼяжемося з вами найближчим часом.')
                                ->rows(2)
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),

                    Section::make('Поля форми')
                        ->schema([
                            Repeater::make('fields')
                                ->label('Поля')
                                ->schema([
                                    TextInput::make('label')
                                        ->label('Назва поля')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (?string $state, callable $set, Get $get): void {
                                            if (filled($get('key'))) {
                                                return;
                                            }

                                            $set('key', \Illuminate\Support\Str::slug((string) $state, '_'));
                                        }),
                                    TextInput::make('key')
                                        ->label('Ключ (name)')
                                        ->required()
                                        ->alphaDash()
                                        ->helperText('Латиниця, для даних заявки'),
                                    Select::make('type')
                                        ->label('Тип')
                                        ->options(LandingFormFieldType::options())
                                        ->default(LandingFormFieldType::Text->value)
                                        ->required()
                                        ->live()
                                        ->native(false),
                                    Toggle::make('required')->label('Обовʼязкове')->default(false),
                                    Select::make('width')
                                        ->label('Ширина')
                                        ->options(['full' => 'На всю ширину', 'half' => 'Половина'])
                                        ->default('full')
                                        ->native(false),
                                    TextInput::make('placeholder')->label('Placeholder'),
                                    TextInput::make('mask')
                                        ->label('Маска')
                                        ->placeholder('+380 (99) 999-99-99')
                                        ->helperText('Для телефону / тексту (IMask на сайті)'),
                                    Textarea::make('help_text')->label('Підказка')->rows(2)->columnSpanFull(),
                                    Repeater::make('options')
                                        ->label('Варіанти (для списку)')
                                        ->schema([
                                            TextInput::make('value')->label('Значення')->required(),
                                        ])
                                        ->defaultItems(0)
                                        ->visible(fn (Get $get): bool => $get('type') === LandingFormFieldType::Select->value)
                                        ->columnSpanFull(),
                                ])
                                ->defaultItems(0)
                                ->collapsible()
                                ->cloneable()
                                ->reorderable()
                                ->orderColumn('sort_order')
                                ->itemLabel(fn (array $state): string => $state['label'] ?? $state['key'] ?? 'Поле')
                                ->columnSpanFull(),
                        ])
                        ->columnSpanFull(),
                ]),
        ];
    }
}
