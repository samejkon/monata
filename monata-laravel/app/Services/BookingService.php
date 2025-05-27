<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingDetail;
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
     * This method retrieves all bookings from the database and returns the
     * result as a Collection.
     *
     * @return \Illuminate\Support\Collection  The collection of booking instances.
     */
    public function get()
    {
        $query  = $this->model->query();

        return $query->get();
    }

    /**
     * Create a new booking with the given data.
     *
     * This method validates room availability and performs a database transaction
     * to create a booking and its associated booking details. It calculates the
     * total payment based on room prices and booking duration.
     *
     * @param  array  $data  The booking data, including booker information,
     *                       guest details, check-in/out dates, deposit, and
     *                       booking details.
     * @return \App\Models\Booking  The created booking instance.
     */
    public function create($data)
    {
        $this->validateRoomAvailability($data['booking_details']);

        return DB::transaction(function () use ($data) {
            $booking = [
                'user_id'        => Arr::get($data, 'booker_id'),
                'guest_name'     => Arr::get($data, 'guest_name'),
                'guest_email'    => Arr::get($data, 'guest_email'),
                'guest_phone'    => Arr::get($data, 'guest_phone'),
                'check_in'       => Arr::get($data, 'check_in'),
                'check_out'      => Arr::get($data, 'check_out'),
                'deposit'        => Arr::get($data, 'deposit'),
                'status'         => BookingStatus::PENDING,
                'note'           => Arr::get($data, 'note'),
            ];

            $total = 0;

            $rooms = $this->getRoomsByBookingDetails($data['booking_details']);

            $bookingDetails = collect($data['booking_details'])->map(function ($item) use (&$total, $rooms) {

                $price = $this->getPriceRoomType($rooms, $item['room_id']);

                $duration = 1;
                $unitHours = 12; //type price of room.

                if (!empty($item['checkin_at']) && !empty($item['checkout_at'])) {
                    $checkin = Carbon::parse($item['checkin_at']);
                    $checkout = Carbon::parse($item['checkout_at']);
                    $diffInHours = $checkin->diffInHours($checkout, false);
                    $duration = $diffInHours > 0 ? max(1, ceil($diffInHours / $unitHours)) : 1;
                }

                $itemTotal = $duration * $price;
                $total += $itemTotal;

                $item['price_per_day'] = $price;

                return $item;
            })->toArray();

            $booking['total_payment'] = $total;

            $recordBooking = $this->model->create($booking);

            $recordBooking->bookingDetails()->createMany($bookingDetails);

            return $recordBooking->pagination();
        });
    }

    /**
     * Retrieve a booking by ID.
     *
     * This method retrieves a booking by its ID and returns the result as a
     * Booking instance.
     *
     * @param  int  $id  The ID of the booking to be retrieved.
     * @return \App\Models\Booking  The booking instance.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findById($id): Booking
    {
        $booking = $this->model->findOrFail($id);

        return $booking;
    }

    /**
     * Update an existing booking with the provided data.
     *
     * This method retrieves a booking by its ID, validates room availability,
     * and updates the booking and its associated booking details within a
     * database transaction. It recalculates the total payment based on room
     * prices and booking duration. Unused booking details are removed.
     *
     * @param  array  $data  The booking data, including user and guest
     *                       information, check-in/out dates, deposit, status,
     *                       note, and booking details.
     * @param  int    $id    The ID of the booking to be updated.
     * @return \App\Models\Booking  The updated booking instance.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update($data, $id)
    {
        $booking = $this->model->findOrFail($id);

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
                'status' => Arr::get($data, 'status', BookingStatus::PENDING),
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

                $duration = 1;
                $unitHours = 12; //type price of room.

                if (!empty($item['checkin_at']) && !empty($item['checkout_at'])) {
                    $checkin = Carbon::parse($item['checkin_at']);
                    $checkout = Carbon::parse($item['checkout_at']);
                    $diffInHours = $checkin->diffInHours($checkout, false);
                    $duration = $diffInHours > 0 ? max(1, ceil($diffInHours / $unitHours)) : 1;
                }

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
     * Get available rooms for a given date range.
     *
     * @param  array  $data
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
                $query->where('checkin_at', '<', $newCheckOut)
                    ->where('checkout_at', '>', $newCheckIn);
            })
            ->pluck('room_id')
            ->unique();

        $availableRooms = $this->room->whereNotIn('id', $occupiedRoomIds)->get();

        return $availableRooms;
    }

    /**
     * Retrieve rooms by booking details.
     *
     * @param  array  $bookingDetails
     * @return \Illuminate\Database\Eloquent\Collection
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
     * Get the price of a room type based on the room ID.
     *
     * This method retrieves the price of the room type associated with the specified room ID
     * from the provided collection of rooms.
     *
     * @param  \Illuminate\Support\Collection  $rooms  The collection of rooms keyed by room ID.
     * @param  int  $roomId  The ID of the room whose type price is to be retrieved.
     * @return float  The price of the room type.
     */
    private function getPriceRoomType($rooms, $roomId): float
    {
        $room = $rooms->get($roomId);

        return $room->roomType->price;
    }

    /**
     * Validate room availability for the given booking details.
     *
     * This method checks if a room is available for the given check-in and check-out dates
     * by querying the booking details table. If a room is not available, an exception is
     * thrown.
     *
     * @param  array  $bookingDetails  The booking details.
     * @param  int|null  $excludeBookingId  The booking ID to be excluded from the query.
     * @return void
     *
     * @throws \Exception  If a room is not available for the selected dates.
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
                    $query->where('checkin_at', '<', $newCheckOut)
                        ->where('checkout_at', '>', $newCheckIn);
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
     * Delete a booking.
     *
     * This method deletes a booking with the specified ID, given that the booking status
     * is either CHECK_OUT, CANCELLED, NO_SHOW, or EXPIRED. If the booking status is not
     * any of these, an exception is thrown.
     *
     * @param  int  $id  The ID of the booking to be deleted.
     * @return bool  True if the booking was deleted successfully, false otherwise.
     *
     * @throws \Exception  If the booking status is not any of CHECK_OUT, CANCELLED, NO_SHOW, or EXPIRED.
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
}
