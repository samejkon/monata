<?php

namespace App\Services;

use App\Models\Room;
use App\Enums\RoomStatus;

class RoomClientService
{
    public function __construct(
        protected Room $model,
    ) {}

    public function find($id)
    {
        $room = $this->model->where('status', RoomStatus::ACTIVE)->findOrFail($id);

        return $room;
    }
}
