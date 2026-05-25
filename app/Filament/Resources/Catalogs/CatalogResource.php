<?php

namespace App\Filament\Resources\Catalogs;

use App\Filament\Concerns\UsesBunnyUpload;
use App\Filament\Resources\Catalogs\Pages\CreateCatalog;
use App\Filament\Resources\Catalogs\Pages\EditCatalog;
use App\Filament\Resources\Catalogs\Pages\ListCatalogs;
use App\Models\Catalog;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CatalogResource extends Resource
{
    use UsesBunnyUpload;

    protected static ?string $model = Catalog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Каталог';

    protected static ?string $pluralModelLabel = 'Каталоги';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
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
                    ->rows(4)
                    ->columnSpanFull(),
                static::bunnyUpload('image_path', 'catalogs')
                    ->label('Зображення')
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->label('Сортування')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Активний')
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Фото')
                    ->disk('bunny')
                    ->height(48),
                TextColumn::make('name')->label('Назва')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug'),
                TextColumn::make('products_count')->counts('products')->label('Товарів'),
                IconColumn::make('is_active')->label('Активний')->boolean(),
                TextColumn::make('sort_order')->label('Сорт.')->sortable(),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCatalogs::route('/'),
            'create' => CreateCatalog::route('/create'),
            'edit' => EditCatalog::route('/{record}/edit'),
        ];
    }
}
