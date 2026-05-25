<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SizeChartRelationManager extends RelationManager
{
    protected static string $relationship = 'sizeChartRows';

    protected static ?string $title = 'Розмірна сітка';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('size_label')->label('Розмір')->required(),
            TextInput::make('bust')->label('Груди'),
            TextInput::make('waist')->label('Талія'),
            TextInput::make('hip')->label('Стегна'),
            TextInput::make('inseam')->label('Шов'),
            TextInput::make('sort_order')->label('Сортування')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('size_label')->label('Розмір'),
                TextColumn::make('bust')->label('Груди'),
                TextColumn::make('waist')->label('Талія'),
                TextColumn::make('hip')->label('Стегна'),
                TextColumn::make('inseam')->label('Шов'),
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
