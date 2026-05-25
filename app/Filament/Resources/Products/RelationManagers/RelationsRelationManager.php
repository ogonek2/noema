<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Enums\ProductRelationType;
use App\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RelationsRelationManager extends RelationManager
{
    protected static string $relationship = 'relations';

    protected static ?string $title = 'Пов\'язані товари';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('related_product_id')
                ->label('Товар')
                ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required()
                ->disableOptionWhen(fn ($value, $livewire) => (int) $value === (int) $livewire->getOwnerRecord()->getKey()),
            Select::make('type')
                ->label('Тип зв\'язку')
                ->options(collect(ProductRelationType::cases())->mapWithKeys(fn (ProductRelationType $t) => [$t->value => $t->label()]))
                ->required()
                ->default(ProductRelationType::Alternative->value),
            TextInput::make('sort_order')->label('Сортування')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('relatedProduct.primary_image_path')
                    ->label('Фото')
                    ->disk('bunny')
                    ->height(48),
                TextColumn::make('relatedProduct.name')->label('Товар'),
                TextColumn::make('relatedProduct.sku')->label('SKU'),
                TextColumn::make('type')->label('Тип')->badge(),
                TextColumn::make('sort_order')->label('Сорт.'),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('sort_order');
    }
}
