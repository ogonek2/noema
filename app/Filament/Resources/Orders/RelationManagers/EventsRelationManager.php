<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsRelationManager extends RelationManager
{
    protected static string $relationship = 'events';

    protected static ?string $title = 'Історія (CRM)';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Час')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('type')->label('Подія')->badge(),
                TextColumn::make('user.name')->label('Користувач')->placeholder('Система'),
                TextColumn::make('body')->label('Деталі')->wrap(),
                TextColumn::make('from_status')->label('З')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('to_status')->label('На')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
