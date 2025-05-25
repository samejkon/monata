<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    protected $table = 'images';

    protected $fillable = [
        'room_id',
        'image_path',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }
}
