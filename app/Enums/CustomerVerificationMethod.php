<?php

namespace App\Enums;

enum CustomerVerificationMethod: int
{
	case MAIL = 0;
	case SMS = 1;
}
