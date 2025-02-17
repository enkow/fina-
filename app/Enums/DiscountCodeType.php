<?php

namespace App\Enums;

enum DiscountCodeType: int
{
	case Percent = 0;
	case Amount = 1;
}
