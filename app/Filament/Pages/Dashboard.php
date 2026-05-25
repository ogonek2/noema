<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\LatestOrdersWidget;
use App\Filament\Widgets\OrdersChartWidget;
use App\Filament\Widgets\OrdersOverviewWidget;
use App\Filament\Widgets\OrdersStatusChartWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            OrdersOverviewWidget::class,
            OrdersChartWidget::class,
            OrdersStatusChartWidget::class,
            LatestOrdersWidget::class,
        ];
    }
}
