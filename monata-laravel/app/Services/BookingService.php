<?php

namespace App\Services;

use App\Enums\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class BookingService
{
    public function __construct(
        protected Booking $model,
        protected User $user,
        protected Room $room
    ) {}

    /**
     * Retrieve all bookings.
     *
     * @return \Illuminate\Support\Collection
     */
    public function get()
    {
        $query  = $this->model->query();

        return $query->get();
    }

    /*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Create a new booking.
     *
     * @param  array  $data
     * @return \App\Models\Booking
     */
    /*******  b145ae3a-511e-4cbf-b54f-da43acbf038d  *******/
    public function create($data)
    {
        $booking = [
            'user_id'        => Arr::get($data, 'booker_id'),
            'guest_name'     => Arr::get($data, 'guest_name'),
            'guest_email'    => Arr::get($data, 'guest_email'),
            'guest_phone'    => Arr::get($data, 'guest_phone'),
            'check_in'       => Arr::get($data, 'check_in'),
            'check_out'      => Arr::get($data, 'check_out'),
            'deposit'        => Arr::get($data, 'deposit'),
            'status'         => Arr::get($data, 'status', BookingStatus::PENDING),
            'note'           => Arr::get($data, 'note'),
            'total_payment'  => 0,
        ];

        $total = 0;

        $bookingDetails = collect($data['booking_details'])->map(function ($item) use (&$total) {
            $room = $this->room->where('id', $item['room_id'])->first();
            dd($room);
            $price = $room?->price ?? 0;

            $duration = 1;
            $unitHours = 12;

            if (!empty($item['checkin_at']) && !empty($item['checkout_at'])) {
                $checkin = Carbon::parse($item['checkin_at']);
                $checkout = Carbon::parse($item['checkout_at']);

                if ($checkout->greaterThan($checkin)) {
                    $diffInHours = $checkout->diffInHours($checkin);
                    $duration = max(1, ceil($diffInHours / $unitHours));
                }
            }

            $itemTotal = $duration * $price;
            $total += $itemTotal;

            return [
                ...$item,
                'price_per_day' => $price,
                'total'              => $itemTotal,
            ];
        })->toArray();

        $booking['total_payment'] = $total;

        $bookingModel = $this->model->create($booking);

        if (!empty($bookingDetails) && method_exists($bookingModel, 'bookingDetails')) {
            foreach ($bookingDetails as &$detail) {
                $detail['booking_id'] = $bookingModel->id;
            }
            $bookingModel->bookingDetails()->createMany($bookingDetails);
        }

        return $bookingModel;
    }
}
