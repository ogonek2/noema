<?php

namespace App\Filament\Widgets;

use App\Services\OrderDashboardStatsService;
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
                ->description('Сума: $'.number_format($stats['today_revenue'], 2))
                ->icon('heroicon-o-shopping-cart'),
            Stat::make('За тиждень', (string) $stats['week_count'])
                ->description('Сума: $'.number_format($stats['week_revenue'], 2))
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
