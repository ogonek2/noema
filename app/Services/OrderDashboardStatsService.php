<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class OrderDashboardStatsService
{
    private const CACHE_TTL_SECONDS = 120;

    /** @return array<string, mixed> */
    public function overview(): array
    {
        return Cache::remember('order_dashboard.overview', self::CACHE_TTL_SECONDS, function (): array {
            $todayStart = today();
            $weekStart = now()->startOfWeek();

            $today = Order::query()
                ->where('created_at', '>=', $todayStart)
                ->selectRaw('COUNT(*) as orders_count, COALESCE(SUM(total), 0) as revenue')
                ->first();

            $week = Order::query()
                ->where('created_at', '>=', $weekStart)
                ->selectRaw('COUNT(*) as orders_count, COALESCE(SUM(total), 0) as revenue')
                ->first();

            $statusCounts = Order::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $pendingPayment = Order::query()
                ->where('payment_status', PaymentStatus::Pending->value)
                ->count();

            $withoutTtn = Order::query()
                ->where('shipping_method', 'nova_poshta_warehouse')
                ->whereNull('ttn_number')
                ->whereNotIn('status', [OrderStatus::Cancelled->value, OrderStatus::Completed->value])
                ->count();

            $awaitingProcessing = (int) ($statusCounts[OrderStatus::Paid->value] ?? 0)
                + (int) ($statusCounts[OrderStatus::Processing->value] ?? 0);

            return [
                'today_count' => (int) ($today->orders_count ?? 0),
                'today_revenue' => (float) ($today->revenue ?? 0),
                'week_count' => (int) ($week->orders_count ?? 0),
                'week_revenue' => (float) ($week->revenue ?? 0),
                'awaiting_processing' => $awaitingProcessing,
                'without_ttn' => $withoutTtn,
                'pending_payment' => $pendingPayment,
            ];
        });
    }

    /** @return array{labels: list<string>, counts: list<int>, revenue: list<float>} */
    public function dailySeries(int $days = 14): array
    {
        return Cache::remember("order_dashboard.daily_series.{$days}", self::CACHE_TTL_SECONDS, function () use ($days): array {
            $start = now()->subDays($days - 1)->startOfDay();

            $rows = Order::query()
                ->where('created_at', '>=', $start)
                ->selectRaw('DATE(created_at) as day')
                ->selectRaw('COUNT(*) as orders_count')
                ->selectRaw('COALESCE(SUM(total), 0) as revenue')
                ->groupBy('day')
                ->orderBy('day')
                ->get()
                ->keyBy(fn ($row) => Carbon::parse($row->day)->toDateString());

            $labels = [];
            $counts = [];
            $revenue = [];

            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i)->startOfDay();
                $key = $date->toDateString();
                $row = $rows->get($key);

                $labels[] = $date->format('d.m');
                $counts[] = (int) ($row->orders_count ?? 0);
                $revenue[] = (float) ($row->revenue ?? 0);
            }

            return compact('labels', 'counts', 'revenue');
        });
    }

    /** @return array{labels: list<string>, counts: list<int>} */
    public function statusSeries(): array
    {
        return Cache::remember('order_dashboard.status_series', self::CACHE_TTL_SECONDS, function (): array {
            $counts = Order::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $labels = [];
            $data = [];

            foreach (OrderStatus::cases() as $status) {
                $count = (int) ($counts[$status->value] ?? 0);

                if ($count === 0) {
                    continue;
                }

                $labels[] = $status->label();
                $data[] = $count;
            }

            return ['labels' => $labels, 'counts' => $data];
        });
    }

    public function flush(): void
    {
        Cache::forget('order_dashboard.overview');
        Cache::forget('order_dashboard.status_series');

        foreach ([7, 14, 30] as $days) {
            Cache::forget("order_dashboard.daily_series.{$days}");
        }
    }
}
