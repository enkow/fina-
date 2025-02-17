<?php

namespace App\Enums;

enum SettlementStatus: int
{
	case Pending = 0;
	case Accepted = 1;
	case Rejected = 2;

	public function localeDescription(): string
	{
		return __('settlements.status.' . $this->value);
	}
}
