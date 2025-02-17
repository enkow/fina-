<?php

namespace App\Enums;

enum RefundStatus: int
{
	case Pending = 0;
	case Confirmed = 1;
	case Rejected = 2;
}
