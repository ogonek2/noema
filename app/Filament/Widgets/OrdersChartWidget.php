<?php

namespace App\Filament\Widgets;

use App\Services\OrderDashboardStatsService;
use Filament\Widgets\ChartWidget;

class OrdersChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $pollingInterval = null;

    protected ?string $heading = 'Замовлення за 14 днів';

    protected function getData(): array
    {
        $series = app(OrderDashboardStatsService::class)->dailySeries(14);

        return [
            'datasets' => [
                [
                    'label' => 'Замовлення',
                    'data' => $series['counts'],
                    'borderColor' => '#18181b',
                ],
                [
                    'label' => 'Сума ($)',
                    'data' => $series['revenue'],
                    'borderColor' => '#71717a',
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y1' => [
                    'type' => 'linear',
                    'position' => 'right',
                    'grid' => ['drawOnChartArea' => false],
                ],
            ],
        ];
    }
}
