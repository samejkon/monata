<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\RevenueRequest;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $service
    ) {}

    /**
     * Get the revenue over time.
     *
     * @param RevenueRequest $request
     * @return JsonResponse
     */
    public function revenue(RevenueRequest $request)
    {
        $data = $this->service->getRevenue($request->validated()['type']);

        return $data;
    }

    /**
     * Get the total number of bookings.
     *
     * @return \Illuminate\Http\JsonResponse The count of bookings.
     */
    public function totalBooking(): JsonResponse
    {
        return $this->service->getTotalBooking();
    }

    /**
     * Get the total number of bookings that are currently pending.
     *
     * @return int The count of pending bookings.
     */

    public function totalBookingPending(): JsonResponse
    {
        return $this->service->getTotalBookingPending();
    }

    /**
     * Get the total number of contacts that are currently unresponse.
     *
     * @return int The count of unresponse contacts.
     */
    public function totalContactUnresponse(): JsonResponse
    {
        return $this->service->getTotalContactUnresponse();
    }
}
