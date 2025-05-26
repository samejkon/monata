<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\RoomStatus;
use App\Models\Image;

class Room extends Model
{
    use SoftDeletes;

    protected $table = 'rooms';

    protected $fillable = [
        'name',
        'room_type_id',
        'thumbnail_path',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => RoomStatus::class,
    ];

    /**
     * Get all images associated with the room.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    /**
     * Get the room type that owns the Room
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
