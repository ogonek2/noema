<?php

namespace App\Filament\Widgets;

use App\Services\OrderDashboardStatsService;
use App\Support\PriceFormat;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrdersOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $stats = app(OrderDashboardStatsService::class)->overview();

        return [
            Stat::make('Замовлення сьогодні', (string) $stats['today_count'])
                ->description('Сума: '.PriceFormat::uah($stats['today_revenue']))
                ->icon('heroicon-o-shopping-cart'),
            Stat::make('За тиждень', (string) $stats['week_count'])
                ->description('Сума: '.PriceFormat::uah($stats['week_revenue']))
                ->icon('heroicon-o-chart-bar'),
            Stat::make('Очікують обробки', (string) $stats['awaiting_processing'])
                ->color('warning')
                ->icon('heroicon-o-clock'),
            Stat::make('Без ТТН (НП)', (string) $stats['without_ttn'])
                ->color('danger')
                ->icon('heroicon-o-truck'),
            Stat::make('Очікує оплати', (string) $stats['pending_payment'])
                ->icon('heroicon-o-banknotes'),
        ];
    }
}
