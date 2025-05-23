<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomType extends Model
{
    use SoftDeletes;

    protected $table = 'room_types';
    protected $fillable = ['name'];

    /**
     * The room properties that belong to the RoomType
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function roomProperties()
    {
        return $this->hasMany(RoomProperty::class);
    }

    /**
     * The properties that belong to the RoomType
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function properties()
    {
        return $this->belongsToMany(Property::class, 'room_properties');
    }
}
