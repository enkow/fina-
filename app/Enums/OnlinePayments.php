<?php

namespace App\Enums;

enum OnlinePayments: string
{
	case External = 'external';
	case Internal = 'internal';
	case Disabled = 'disabled';
}
