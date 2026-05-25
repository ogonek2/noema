<?php

namespace App\Filament\Widgets;

use App\Services\OrderDashboardStatsService;
use Filament\Widgets\ChartWidget;

class OrdersStatusChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $pollingInterval = null;

    protected ?string $heading = 'Замовлення за статусами';

    protected function getData(): array
    {
        $series = app(OrderDashboardStatsService::class)->statusSeries();

        return [
            'datasets' => [
                [
                    'data' => $series['counts'],
                    'backgroundColor' => ['#18181b', '#3f3f46', '#71717a', '#a1a1aa', '#d4d4d8', '#e4e4e7'],
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
