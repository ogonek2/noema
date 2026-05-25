<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Товари';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label('')->disk('bunny')->height(40),
                TextColumn::make('product_name')->label('Товар'),
                TextColumn::make('sku')->label('SKU'),
                TextColumn::make('color_name')->label('Колір'),
                TextColumn::make('size')->label('Розмір'),
                TextColumn::make('quantity')->label('К-сть'),
                TextColumn::make('unit_price')->label('Ціна')->money('USD'),
                TextColumn::make('line_total')->label('Сума')->money('USD'),
                TextColumn::make('notes')->label('Нотатка')->limit(30),
            ])
            ->paginated(false);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
