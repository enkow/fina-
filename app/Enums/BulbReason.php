<?php

namespace App\Enums;

enum BulbReason: string
{
	case AUTOSYNC = 'auto-sync';
	case MANUALSYNC = 'manual-sync';
	case MANUAL = 'manual';
	case RESERVATION = 'reservation';
}
