<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Enums\ProductLength;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Розміри';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('sku')->label('SKU')->required(),
            TextInput::make('name')->label('Назва варіанту'),
            Select::make('size')
                ->label('Розмір')
                ->options([
                    'XXS' => 'XXS',
                    'XS' => 'XS',
                    'S' => 'S',
                    'M' => 'M',
                    'L' => 'L',
                    'XL' => 'XL',
                    '2XL' => '2XL',
                ]),
            Select::make('length')
                ->label('Довжина')
                ->options(collect(ProductLength::cases())->mapWithKeys(fn (ProductLength $l) => [$l->value => $l->label()]))
                ->default(ProductLength::Regular->value),
            TextInput::make('price')->label('Ціна варіанту')->numeric()->prefix('₴')->default(100),
            TextInput::make('stock_quantity')->label('Залишок')->numeric()->default(0),
            Toggle::make('is_active')->label('Активний')->default(true),
            TextInput::make('sort_order')->label('Сортування')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')->label('SKU'),
                TextColumn::make('size')->label('Розмір'),
                TextColumn::make('length')->label('Довжина'),
                TextColumn::make('price')->label('Ціна')->money('UAH'),
                TextColumn::make('stock_quantity')->label('Склад'),
                IconColumn::make('is_active')->boolean(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('sort_order');
    }
}
