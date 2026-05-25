<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestOrdersWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Останні замовлення';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest()->limit(8))
            ->columns([
                TextColumn::make('number')->label('№'),
                TextColumn::make('customer_name')->label('Клієнт')->limit(20),
                TextColumn::make('status')->label('Статус')->badge(),
                TextColumn::make('total')->label('Сума')->money('UAH'),
                TextColumn::make('created_at')->label('Дата')->since(),
            ])
            ->recordUrl(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]));
    }
}
