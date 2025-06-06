<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\RevenueRequest;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $service
    ) {}

    public function revenue(RevenueRequest $request)
    {
        $data = $this->service->getRevenue($request->validated()['type']);

        return $data;
    }
}
