<?php

namespace App\Filament\Resources\SizePresets;

use App\Filament\Resources\SizePresets\Pages\CreateSizePreset;
use App\Filament\Resources\SizePresets\Pages\EditSizePreset;
use App\Filament\Resources\SizePresets\Pages\ListSizePresets;
use App\Filament\Resources\SizePresets\RelationManagers\ChartRowsRelationManager;
use App\Filament\Resources\SizePresets\RelationManagers\VariantsRelationManager;
use App\Models\SizePreset;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SizePresetResource extends Resource
{
    protected static ?string $model = SizePreset::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected static string|\UnitEnum|null $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Пресет розмірів';

    protected static ?string $pluralModelLabel = 'Пресети розмірів';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('sizePreset')->tabs([
                Tab::make('Основне')->schema([
                    TextInput::make('name')
                        ->label('Назва')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (?string $state, callable $set) => $set('slug', Str::slug((string) $state))),
                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Textarea::make('description')
                        ->label('Опис')
                        ->rows(3)
                        ->columnSpanFull(),
                    Toggle::make('is_active')->label('Активний')->default(true),
                    TextInput::make('sort_order')->label('Сортування')->numeric()->default(0),
                ])->columns(2),
                Tab::make('Таблиця та довжина')->schema([
                    Textarea::make('size_chart_intro')
                        ->label('Вступ до таблиці розмірів')
                        ->rows(3)
                        ->columnSpanFull(),
                    Textarea::make('length_guide')
                        ->label('Рекомендації по довжині (Petite / Regular / Tall)')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Назва')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug'),
                TextColumn::make('chart_rows_count')->counts('chartRows')->label('Рядків сітки'),
                TextColumn::make('variants_count')->counts('variants')->label('Розмірів'),
                TextColumn::make('products_count')->counts('products')->label('Товарів'),
                IconColumn::make('is_active')->label('Активний')->boolean(),
                TextColumn::make('sort_order')->label('Сорт.')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Активний'),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            ChartRowsRelationManager::class,
            VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSizePresets::route('/'),
            'create' => CreateSizePreset::route('/create'),
            'edit' => EditSizePreset::route('/{record}/edit'),
        ];
    }
}
