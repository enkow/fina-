<?php

namespace App\Enums;

enum AnnouncementType: int
{
	case Panel = 0;
	case Widget = 1;
	case Calendar = 2;
}
