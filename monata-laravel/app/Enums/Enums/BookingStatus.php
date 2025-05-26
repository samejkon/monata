<?php

namespace App\Enums\Enums;

enum BookingStatus
{
    const PENDING = 1;
    const CONFIRMED = 2;
    const CHECK_IN = 3;
    const CHECK_OUT = 4;
    const CANCELLED = 5;
}
