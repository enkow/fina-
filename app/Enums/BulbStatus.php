<?php

namespace App\Enums;

enum BulbStatus: int
{
	case ON = 1;
	case OFF = 2;
	case OFFLINE = 3;
}
