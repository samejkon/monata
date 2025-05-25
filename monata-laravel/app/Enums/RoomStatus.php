<?php

namespace App\Enums;

enum RoomStatus:int
{
    case NORMAL = 1;
    case BOOKED = 2;
    case OCCUPIED = 3;
    case CLEANING = 4;
    case INACTIVE = 5;

    public function label()
    {
        return match ($this) {
            self::NORMAL => 'Normal',
            self::BOOKED => 'Booked',
            self::OCCUPIED => 'Occupied',
            self::CLEANING => 'Cleaning',
            self::INACTIVE => 'Inactive',
        };
    }
}
