<?php

namespace App\Enums;

enum ReservationReturnStatus: int
{
	case Pending = 0;
	case Returned = 1;
	case Rejected = 2;
}
