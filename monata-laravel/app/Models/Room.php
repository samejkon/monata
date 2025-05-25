<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\RoomStatus;
use App\Models\Image;

class Room extends Model
{
    protected $table = 'rooms';

    protected $fillable = [
        'name',
        'room_type_id',
        'price',
        'thumbnail_path',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => RoomStatus::class,
    ];

    public function image(): HasMany
    {
        return $this->hasMany(Image::class);
    }
}
