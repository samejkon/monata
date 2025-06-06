<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(
        protected Booking $model
    ) {}

    public function getRevenue($type)
    {
        switch ($type) {
            case 'day':
                return DB::table('bookings')
                    ->selectRaw('DATE(created_at) AS revenue_date, SUM(total_payment) AS revenue')
                    ->where('created_at', '>=', now()->subDays(6)->startOfDay())
                    ->groupBy('revenue_date')
                    ->orderBy('revenue_date', 'ASC')
                    ->get();

            case 'week':
                return DB::table('bookings')
                    ->selectRaw('YEARWEEK(created_at, 1) AS revenue_week, SUM(total_payment) AS revenue')
                    ->where('created_at', '>=', now()->subWeeks(8)->startOfDay())
                    ->groupBy('revenue_week')
                    ->orderBy('revenue_week', 'ASC')
                    ->get();

            case 'month':
                return DB::table('bookings')
                    ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') AS revenue_month, SUM(total_payment) AS revenue")
                    ->whereYear('created_at', now()->year)
                    ->groupBy('revenue_month')
                    ->orderBy('revenue_month', 'ASC')
                    ->get();

            default:
                throw new \InvalidArgumentException('Invalid revenue type');
        }
    }
}
