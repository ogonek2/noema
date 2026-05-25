<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Enums\CustomizationOptionType;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CustomizationsRelationManager extends RelationManager
{
    protected static string $relationship = 'customizationOptions';

    protected static ?string $title = 'Індивідуальний пошив';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Назва')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn (?string $state, callable $set) => $set('slug', Str::slug((string) $state))),
            TextInput::make('slug')->label('Slug')->required(),
            Textarea::make('description')->label('Опис')->rows(2),
            Select::make('type')
                ->label('Тип поля')
                ->options(collect(CustomizationOptionType::cases())->mapWithKeys(fn (CustomizationOptionType $t) => [$t->value => $t->label()]))
                ->required()
                ->live(),
            KeyValue::make('options')
                ->label('Опції (для select)')
                ->visible(fn (callable $get) => $get('type') === CustomizationOptionType::Select->value),
            TextInput::make('price_delta')->label('Доплата')->numeric()->prefix('₴')->default(0),
            Toggle::make('is_required')->label('Обов\'язково'),
            Toggle::make('is_active')->label('Активно')->default(true),
            TextInput::make('sort_order')->label('Сортування')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Опція'),
                TextColumn::make('type')->label('Тип'),
                TextColumn::make('price_delta')->label('Доплата')->money('UAH'),
                IconColumn::make('is_required')->label('Обов\'язк.')->boolean(),
                IconColumn::make('is_active')->label('Активно')->boolean(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('sort_order');
    }
}
