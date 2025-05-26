<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\CreateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Services\BookingService;

class AdminBookingController extends Controller
{

    public function __construct(
        protected BookingService $service
    ) {}

    /**
     * Get all bookings.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $data = $this->service->get();

        return BookingResource::collection($data);
    }

    /**
     * Create a new booking.
     *
     * @param  \App\Http\Requests\Booking\CreateBookingRequest  $request
     * @return \App\Http\Resources\BookingResource
     */
    public function store(CreateBookingRequest $request)
    {
        $data = $this->service->create($request->validated());

        return new BookingResource($data);
    }
}
