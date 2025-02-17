<?php

namespace App\Enums;

enum CustomerFilter: int
{
	case New = 1;
	case MostLoyal = 2;
	case Inactive = 3;
	case FirstReservation = 4;

	public function localeDescription(): string
	{
		return __('customers.filter.' . $this->value);
	}
}
