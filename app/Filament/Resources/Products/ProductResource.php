<?php

namespace App\Filament\Resources\Products;

use App\Filament\Concerns\UsesBunnyUpload;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\RelationManagers\CustomizationsRelationManager;
use App\Filament\Resources\Products\RelationManagers\DetailItemsRelationManager;
use App\Filament\Resources\Products\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\Products\RelationManagers\RelationsRelationManager;
use App\Filament\Resources\Products\RelationManagers\SizeChartRelationManager;
use App\Filament\Resources\Products\RelationManagers\VariantsRelationManager;
use App\Models\Catalog;
use App\Models\Product;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    use UsesBunnyUpload;

    protected static ?string $model = Product::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|\UnitEnum|null $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Товар';

    protected static ?string $pluralModelLabel = 'Товари';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('product')->tabs([
                Tab::make('Основне')->schema([
                    Select::make('catalog_id')
                        ->label('Каталог')
                        ->options(fn () => Catalog::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->live(),
                    TextInput::make('model_slug')
                        ->label('Модель (slug)')
                        ->required()
                        ->helperText('Один slug для всіх кольорів однієї моделі (напр. celestia)'),
                    TextInput::make('color_name')->label('Колір'),
                    TextInput::make('color_slug')->label('Slug кольору'),
                    TextInput::make('color_hex')->label('HEX кольору')->placeholder('#1A1A1A'),
                    TextInput::make('name')
                        ->label('Назва')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (?string $state, callable $set) => $set('slug', Str::slug((string) $state))),
                    TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true),
                    TextInput::make('sku')->label('SKU')->required()->unique(ignoreRecord: true),
                    TextInput::make('subtitle')->label('Підзаголовок')->columnSpanFull(),
                    Textarea::make('short_description')->label('Короткий опис')->rows(3)->columnSpanFull(),
                    RichEditor::make('description')->label('Опис')->columnSpanFull(),
                    TextInput::make('price')->label('Ціна')->numeric()->prefix('$')->required()->default(100),
                    TextInput::make('compare_at_price')->label('Стара ціна')->numeric()->prefix('$'),
                    static::bunnyUpload('primary_image_path', 'products')
                        ->label('Головне фото')
                        ->columnSpanFull(),
                    Toggle::make('is_active')->label('Активний')->default(true),
                    Toggle::make('is_featured')->label('Рекомендований'),
                    TextInput::make('sort_order')->label('Сортування')->numeric()->default(0),
                ])->columns(2),
                Tab::make('Посадка та тканина')->schema([
                    Textarea::make('fit_summary')->label('Посадка (коротко)')->rows(2)->columnSpanFull(),
                    RichEditor::make('fit_details')->label('Деталі посадки')->columnSpanFull(),
                    Textarea::make('fabric_summary')->label('Тканина (коротко)')->rows(2)->columnSpanFull(),
                    RichEditor::make('fabric_details')->label('Склад та властивості тканини')->columnSpanFull(),
                    RichEditor::make('care_instructions')->label('Догляд')->columnSpanFull(),
                ]),
                Tab::make('Розмірна сітка')->schema([
                    Textarea::make('size_chart_intro')->label('Вступ до таблиці розмірів')->rows(3)->columnSpanFull(),
                    Textarea::make('length_guide')->label('Рекомендації по довжині (Petite / Regular / Tall)')->rows(4)->columnSpanFull(),
                ]),
                Tab::make('SEO')->schema([
                    TextInput::make('meta_title')->label('Meta title')->columnSpanFull(),
                    Textarea::make('meta_description')->label('Meta description')->rows(3)->columnSpanFull(),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('primary_image_path')->label('Фото')->disk('bunny')->height(56),
                TextColumn::make('name')->label('Назва')->searchable()->sortable(),
                TextColumn::make('sku')->label('SKU')->searchable(),
                TextColumn::make('catalog.name')->label('Каталог')->sortable(),
                TextColumn::make('color_name')->label('Колір'),
                TextColumn::make('model_slug')->label('Модель'),
                TextColumn::make('price')->label('Ціна')->money('USD'),
                TextColumn::make('variants_count')->counts('variants')->label('Варіанти'),
                IconColumn::make('is_active')->label('Активний')->boolean(),
                IconColumn::make('is_featured')->label('Топ')->boolean(),
            ])
            ->filters([
                SelectFilter::make('catalog_id')->label('Каталог')->relationship('catalog', 'name'),
                TernaryFilter::make('is_active')->label('Активний'),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            ImagesRelationManager::class,
            VariantsRelationManager::class,
            DetailItemsRelationManager::class,
            SizeChartRelationManager::class,
            RelationsRelationManager::class,
            CustomizationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
