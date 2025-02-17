<?php

namespace App\Enums;

enum ReminderMethod: string
{
	case Mail = 'mail';
	case Sms = 'sms';
}
