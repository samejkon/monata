<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\CheckRoomAvaiableRequest;
use App\Http\Requests\Booking\CreateBookingRequest;
use App\Http\Requests\Booking\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\RoomResource;
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

    /**
     * Check room availability for the given date range.
     *
     * This method retrieves all rooms that are available for booking
     * between the specified check-in and check-out dates. It excludes
     * rooms that are currently booked or occupied during this period.
     *
     * @param  \App\Http\Requests\Booking\CheckRoomAvaiableRequest  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function checkRoomAvailability(CheckRoomAvaiableRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $data = $this->service->checkRoom($request->validated());

        return RoomResource::collection($data);
    }

    /**
     * Update the specified booking in storage.
     *
     * @param  \App\Http\Requests\Booking\UpdateBookingRequest  $request
     * @param  int  $id
     * @return \App\Http\Resources\BookingResource
     */
    public function update(UpdateBookingRequest $request, $id): BookingResource
    {
        $data = $this->service->update($request->validated(), $id);

        return new BookingResource($data);
    }

    /**
     * Remove the specified booking from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): \Illuminate\Http\Response
    {
        $this->service->delete($id);

        return response()->noContent();
    }
}
