<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingDetail extends Model
{
    use SoftDeletes;

    protected $table = 'booking_details';
    protected $fillable = [
        'id',
        'room_id',
        'booking_id',
        'checkin_at',
        'checkout_at',
        'price_per_day',
    ];

    protected $casts = [
        'checkin_at' => 'datetime',
        'checkout_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function rooms()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
