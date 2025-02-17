<?php

namespace App\Enums;

enum ReservationSlotStatus: int
{
	case Pending = 0;
	case Confirmed = 1;
	case Expired = 2;

	public function locale(): string
	{
		return __('reservation.statuses.' . $this->value);
	}
}
