<?php

namespace App\Enums;

enum ReservationSlotCancelationType: int
{
	case Customer = 1;
	case Club = 2;
	case System = 3;

	public function locale(): string
	{
		return __(
			'reservation.status.' .
				match ($this->value) {
					1 => 'canceled-by-customer',
					2 => 'canceled-by-club',
					3 => 'canceled-by-system',
				}
		);
	}
}
