<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\InvoiceDetail;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        protected Booking $model,
        protected User $user,
        protected Room $room,
        protected BookingDetail $bookingDetail
    ) {}

    /**
     * Get all bookings.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get(): \Illuminate\Database\Eloquent\Collection
    {
        $query  = $this->model->query();

        return $query->get();
    }

    /**
     * Create a booking.
     *
     * @param array $data
     * @return \App\Models\Booking
     */
    public function create($data): Booking
    {
        $this->validateRoomAvailability($data['booking_details']);

        return DB::transaction(function () use ($data) {
            $booking = [
                'user_id'        => Arr::get($data, 'booker_id'),
                'guest_name'     => Arr::get($data, 'guest_name'),
                'guest_email'    => Arr::get($data, 'guest_email'),
                'guest_phone'    => Arr::get($data, 'guest_phone'),
                'deposit'        => Arr::get($data, 'deposit'),
                'status'         => BookingStatus::CONFIRMED,
                'note'           => Arr::get($data, 'note'),
            ];

            $total = 0;

            $rooms = $this->getRoomsByBookingDetails($data['booking_details']);

            $bookingDetails = collect($data['booking_details'])->map(function ($item) use (&$total, $rooms) {
                $price = $this->getPriceRoomType($rooms, $item['room_id']);

                $duration = config('room.duration');
                $unitHours = config('room.unit_hours');

                $duration = $this->calculateDuration($item['checkin_at'], $item['checkout_at'], $unitHours);

                $itemTotal = $duration * $price;
                $total += $itemTotal;

                $item['price_per_day'] = $price;

                return $item;
            })->toArray();

            $booking['total_payment'] = $total;

            $recordBooking = $this->model->create($booking);

            $recordBooking->bookingDetails()->createMany($bookingDetails);

            return $recordBooking;
        });
    }

    /**
     * Find a booking by its ID.
     *
     * @param  int  $id  The booking ID.
     * @return \App\Models\Booking
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findById($id): Booking
    {
        $booking = $this->model->with([
            'bookingDetails.rooms.roomType',
        ])->findOrFail($id);

        return $booking;
    }

    /**
     * Update a booking.
     *
     * @param  array  $data
     * @param  int  $id
     * @return \App\Models\Booking
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update($data, $id)
    {
        $booking = $this->model->whereIn('status', [
            BookingStatus::PENDING,
            BookingStatus::CONFIRMED,
            BookingStatus::CHECK_IN,
        ])->findOrFail($id);

        $this->validateRoomAvailability($data['booking_details'], $booking->id);

        return DB::transaction(function () use ($booking, $data) {
            $bookingUpdate = [
                'user_id'        => Arr::get($data, 'user_id'),
                'guest_name'     => Arr::get($data, 'guest_name'),
                'guest_email'    => Arr::get($data, 'guest_email'),
                'guest_phone'    => Arr::get($data, 'guest_phone'),
                'check_in'       => Arr::get($data, 'check_in'),
                'check_out'      => Arr::get($data, 'check_out'),
                'deposit'        => Arr::get($data, 'deposit'),
                'status'         => Arr::get($data, 'status', BookingStatus::CONFIRMED),
                'note'           => Arr::get($data, 'note'),
            ];

            $idBookingDetails = collect($data['booking_details'])->pluck('id')->toArray();

            BookingDetail::where('booking_id', $booking->id)
                ->whereNotIn('id', $idBookingDetails)
                ->delete();

            $rooms = $this->getRoomsByBookingDetails($data['booking_details']);

            $total = 0;

            $bookingDetails = collect($data['booking_details'])->map(function ($item) use (&$total, $rooms) {
                $price = $this->getPriceRoomType($rooms, $item['room_id']);

                $duration = config('room.duration');
                $unitHours = config('room.unit_hours');

                $duration = $this->calculateDuration($item['checkin_at'], $item['checkout_at'], $unitHours);

                $itemTotal = $duration * $price;
                $total += $itemTotal;

                $item['price_per_day'] = $price;

                return $item;
            })->toArray();

            $bookingUpdate['total_payment'] = $total;

            $booking->update($bookingUpdate);
            $booking->bookingDetails()->upsert($bookingDetails, ['id']);

            return $booking;
        });
    }

    /**
     * Find available rooms for a given date range.
     *
     * This method retrieves all rooms that are not occupied during the specified
     * check-in and check-out dates. It excludes rooms that have a booking with
     * one of the following statuses: pending, confirmed, or check-in.
     *
     * @param  array  $data  The input data.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function checkRoom($data): \Illuminate\Database\Eloquent\Collection
    {
        $newCheckIn = $data['checkin_at'];
        $newCheckOut = $data['checkout_at'];

        $occupiedRoomIds = $this->bookingDetail->whereHas('booking', function ($query) {
            $query->whereIn('status', [
                BookingStatus::PENDING,
                BookingStatus::CONFIRMED,
                BookingStatus::CHECK_IN
            ]);
        })
            ->where(function ($query) use ($newCheckIn, $newCheckOut) {
                $this->scopeTimeOverlap($query, $newCheckIn, $newCheckOut);
            })
            ->pluck('room_id')
            ->unique();

        $availableRooms = $this->room->whereNotIn('id', $occupiedRoomIds)->get();

        return $availableRooms;
    }

    /**
     * Retrieve rooms by booking details.
     *
     * This method takes an array of booking details and returns a collection of
     * rooms keyed by their ID. The rooms are eager loaded with their room type.
     *
     * @param  array  $bookingDetails  The booking details.
     * @return \Illuminate\Support\Collection
     */
    private function getRoomsByBookingDetails(array $bookingDetails)
    {
        $roomIds = collect($bookingDetails)->pluck('room_id')->unique();

        return $this->room->with('roomType')
            ->whereIn('id', $roomIds)
            ->get()
            ->keyBy('id');
    }

    /**
     * Retrieve the price of a room type given the room ID.
     *
     * This method takes a collection of rooms and a room ID, and returns the
     * price of the room type associated with the room.
     *
     * @param  \Illuminate\Support\Collection  $rooms  The collection of rooms.
     * @param  int  $roomId  The room ID.
     * @return float
     */
    private function getPriceRoomType($rooms, $roomId): float
    {
        $room = $rooms->get($roomId);

        return $room->roomType->price;
    }

    /**
     * Validate room availability for the given booking details.
     *
     * This method iterates over the booking details and checks if the room is
     * available for the selected dates. It excludes bookings with one of the
     * following statuses: pending, confirmed, or check-in. If the room is not
     * available, it throws an exception with a descriptive message.
     *
     * @param  array  $bookingDetails  The booking details.
     * @param  int|null  $excludeBookingId  The booking ID to exclude from the check.
     * @return void
     * @throws \Exception
     */
    private function validateRoomAvailability(array $bookingDetails, $excludeBookingId = null): void
    {
        foreach ($bookingDetails as $detail) {
            $roomId = $detail['room_id'];
            $newCheckIn = $detail['checkin_at'];
            $newCheckOut = $detail['checkout_at'];

            $query = $this->bookingDetail->where('room_id', $roomId)
                ->whereHas('booking', function ($query) {
                    $query->whereIn('status', [
                        BookingStatus::PENDING,
                        BookingStatus::CONFIRMED,
                        BookingStatus::CHECK_IN
                    ]);
                })
                ->where(function ($query) use ($newCheckIn, $newCheckOut) {
                    $this->scopeTimeOverlap($query, $newCheckIn, $newCheckOut);
                });

            if ($excludeBookingId) {
                $query->where('booking_id', '!=', $excludeBookingId);
            }

            $isAvailable = !$query->exists();

            if (!$isAvailable) {
                throw new \Exception("Room ID {$roomId} is not available for the selected dates.");
            }
        }
    }

    /**
     * Calculate the duration of a booking in terms of the given time unit.
     *
     * This method takes the check-in and check-out times of a booking and
     * returns the duration of the booking in terms of the given time unit.
     * If the check-in or check-out times are empty, the method returns 1.
     *
     * @param  string  $checkin  The check-in time.
     * @param  string  $checkout  The check-out time.
     * @param  int  $unitHours  The time unit in hours. Defaults to 12.
     * @return int
     */
    private function calculateDuration($checkin, $checkout, $unitHours = 12): int
    {
        if (empty($checkin) || empty($checkout)) {
            return 1;
        }

        $checkin = Carbon::parse($checkin);
        $checkout = Carbon::parse($checkout);
        $diffInHours = $checkin->diffInHours($checkout, false);

        return $diffInHours > 0 ? max(1, ceil($diffInHours / $unitHours)) : 1;
    }

    /**
     * Scope a query to only include overlapping time ranges.
     *
     * This scope takes a start and end time, and returns a query that only
     * includes records that have a check-in time before the end time and a
     * check-out time after the start time.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $start  The start time.
     * @param  string  $end  The end time.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeTimeOverlap($query, $start, $end)
    {
        return $query->where(function ($q) use ($start, $end) {
            $q->where('checkin_at', '<', $end)
                ->where('checkout_at', '>', $start);
        });
    }

    /**
     * Delete a booking.
     *
     * This method deletes a booking by its ID. The booking must have a status
     * of CHECK_OUT, CANCELLED, NO_SHOW, or EXPIRED. If the booking is not found
     * or its status is not one of the above, an exception is thrown.
     *
     * @param  int  $id  The ID of the booking to be deleted.
     * @return bool  True if the booking was successfully deleted, false otherwise.
     * @throws \Exception
     */
    public function delete($id): bool
    {
        $booking = $this->model->where('id', $id)
            ->whereIn('status', [
                BookingStatus::CHECK_OUT,
                BookingStatus::CANCELLED,
                BookingStatus::NO_SHOW,
                BookingStatus::EXPIRED
            ])
            ->first();

        if (!$booking) {
            throw new \Exception("Booking can't delete.");
        }

        return $booking->delete();
    }

    /**
     * Confirm a booking by its ID.
     *
     * This method changes the status of a booking from PENDING to CONFIRMED.
     * It retrieves the booking by its ID, ensuring that it is currently
     * in the PENDING status. If the booking is found, its status is updated
     * to CONFIRMED and saved.
     *
     * @param  int  $id  The ID of the booking to confirm.
     * @return bool  True if the booking status was successfully updated, false otherwise.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException  If the booking is not found.
     */

    public function confirm($id): bool
    {
        $booking = $this->model->where('status', BookingStatus::PENDING)->findOrFail($id);

        $booking->status = BookingStatus::CONFIRMED;

        return $booking->save();
    }

    /**
     * Check in a guest by the booking ID.
     *
     * This method retrieves a booking by its ID, given that the booking status
     * is CONFIRMED. If the booking status is not CONFIRMED, an exception is
     * thrown. It then updates the booking status to CHECK_IN and saves the
     * booking.
     *
     * @param  int  $id  The ID of the booking to be checked in.
     * @return bool  True if the booking status was successfully updated, false otherwise.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException  If the booking is not found.
     */
    public function checkInGuest($id): bool
    {
        $booking = $this->model->where('status', BookingStatus::CONFIRMED)->findOrFail($id);

        $booking->status = BookingStatus::CHECK_IN;
        $booking->check_in = Carbon::now();

        return $booking->save();
    }

    /**
     * Check out a booking and update its total payment and status.
     *
     * This method retrieves a booking by its ID, calculates the total service cost
     * from the invoice details table, and then updates the booking's total payment
     * and status. The new status is set to 'checked_out', and the total payment
     * is set to the calculated total service cost.
     *
     * @param  int  $bookingId  The ID of the booking to be checked out.
     * @return \App\Models\Booking  The updated booking instance.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException  If the booking is not found.
     */
    public function checkout(int $bookingId): Booking
    {
        return DB::transaction(function () use ($bookingId) {
            $booking = Booking::findOrFail($bookingId);

            $priceRoom = $booking->total_payment;

            $priceService = InvoiceDetail::where('booking_id', $bookingId)
                ->select(DB::raw('SUM(price * quantity) as total'))
                ->value('total');

            $totalPayment = $priceRoom + $priceService;

            $booking->update([
                'total_payment' => $totalPayment,
                'status' => BookingStatus::CHECK_OUT,
            ]);

            return $booking;
        });
    }
}
