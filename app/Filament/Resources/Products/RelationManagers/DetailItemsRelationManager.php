<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DetailItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'detailItems';

    protected static ?string $title = 'Деталі (як у FIGS)';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('label')->label('Пункт')->required(),
            Textarea::make('content')->label('Опис')->rows(2),
            TextInput::make('sort_order')->label('Сортування')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->label('Пункт'),
                TextColumn::make('content')->label('Опис')->limit(80),
                TextColumn::make('sort_order')->label('Сорт.'),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }
}
