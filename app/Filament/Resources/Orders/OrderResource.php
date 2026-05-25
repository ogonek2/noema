<?php

namespace App\Filament\Resources\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Orders\RelationManagers\EventsRelationManager;
use App\Filament\Resources\Orders\RelationManagers\ItemsRelationManager;
use App\Models\Order;
use App\Models\User;
use App\Support\EnumOptions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Замовлення';

    protected static ?string $pluralModelLabel = 'Замовлення';

    protected static ?string $recordTitleAttribute = 'number';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('order')->tabs([
                Tab::make('Статус')->schema([
                    Select::make('status')
                        ->label('Статус замовлення')
                        ->options(EnumOptions::map(OrderStatus::class))
                        ->required(),
                    Select::make('payment_status')
                        ->label('Оплата')
                        ->options(EnumOptions::map(PaymentStatus::class))
                        ->required(),
                    Select::make('assigned_to')
                        ->label('Менеджер')
                        ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),
                    Textarea::make('internal_notes')
                        ->label('Внутрішні нотатки')
                        ->rows(4)
                        ->columnSpanFull(),
                ])->columns(2),
                Tab::make('Доставка / ТТН')->schema([
                    TextInput::make('ttn_number')->label('Номер ТТН')->disabled(),
                    Select::make('ttn_status')
                        ->label('Статус ТТН')
                        ->options(EnumOptions::map(\App\Enums\TtnStatus::class))
                        ->nullable(),
                    TextInput::make('shipment_weight')
                        ->label('Вага (кг)')
                        ->numeric()
                        ->minValue(0.1)
                        ->step(0.1),
                    TextInput::make('shipment_seats')
                        ->label('Місць')
                        ->numeric()
                        ->minValue(1)
                        ->default(1),
                ])->columns(2),
            ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Замовлення')->schema([
                TextEntry::make('number')->label('Номер'),
                TextEntry::make('status')->label('Статус')->badge(),
                TextEntry::make('payment_status')->label('Оплата')->badge(),
                TextEntry::make('payment_method')->label('Спосіб оплати'),
                TextEntry::make('total')->label('Сума')->money('USD'),
                TextEntry::make('created_at')->label('Створено')->dateTime('d.m.Y H:i'),
            ])->columns(3),
            Section::make('Клієнт')->schema([
                TextEntry::make('customer_name')->label('Імʼя'),
                TextEntry::make('customer_phone')->label('Телефон'),
                TextEntry::make('customer_email')->label('Email'),
                TextEntry::make('customer_notes')->label('Коментар клієнта')->columnSpanFull(),
            ])->columns(3),
            Section::make('Доставка')->schema([
                TextEntry::make('shipping_method')->label('Спосіб'),
                TextEntry::make('shipping_city_name')->label('Місто'),
                TextEntry::make('shipping_warehouse_name')->label('Відділення'),
                TextEntry::make('shipping_address')->label('Адреса')->columnSpanFull(),
            ])->columns(3),
            Section::make('Нова Пошта')->schema([
                TextEntry::make('ttn_number')->label('ТТН')->placeholder('—'),
                TextEntry::make('ttn_status')->label('Статус ТТН')->placeholder('—'),
                TextEntry::make('shipment_weight')->label('Вага')->suffix(' кг'),
                TextEntry::make('assignee.name')->label('Менеджер')->placeholder('—'),
                TextEntry::make('internal_notes')->label('Внутрішні нотатки')->columnSpanFull(),
            ])->columns(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('№')->searchable()->sortable(),
                TextColumn::make('customer_name')->label('Клієнт')->searchable()->limit(24),
                TextColumn::make('customer_phone')->label('Телефон'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label())
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label('Оплата')
                    ->badge()
                    ->formatStateUsing(fn (PaymentStatus $state): string => $state->label()),
                TextColumn::make('payment_method')
                    ->label('Спосіб оплати')
                    ->formatStateUsing(fn (?PaymentMethod $state): string => $state?->label() ?? '—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('shipping_method')
                    ->label('Доставка')
                    ->formatStateUsing(fn (ShippingMethod $state): string => $state->label())
                    ->toggleable(),
                TextColumn::make('total')->label('Сума')->money('USD')->sortable(),
                TextColumn::make('ttn_number')->label('ТТН')->placeholder('—')->toggleable(),
                TextColumn::make('assignee.name')->label('Менеджер')->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->label('Дата')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(EnumOptions::map(OrderStatus::class)),
                SelectFilter::make('payment_status')
                    ->label('Оплата')
                    ->options(EnumOptions::map(PaymentStatus::class)),
                SelectFilter::make('payment_method')
                    ->label('Спосіб оплати')
                    ->options(EnumOptions::map(PaymentMethod::class)),
                SelectFilter::make('shipping_method')
                    ->label('Доставка')
                    ->options(EnumOptions::map(ShippingMethod::class)),
                Filter::make('has_ttn')
                    ->label('З ТТН')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('ttn_number')),
                Filter::make('needs_ttn')
                    ->label('Потрібна ТТН (НП)')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereIn('shipping_method', [ShippingMethod::NovaPoshtaWarehouse->value])
                        ->whereNull('ttn_number')
                        ->whereNotIn('status', [OrderStatus::Cancelled->value, OrderStatus::Completed->value])),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            EventsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
