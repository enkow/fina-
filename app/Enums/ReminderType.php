<?php

namespace App\Enums;

enum ReminderType: string
{
	case RatingRequest = 'rating_request';
	case NewReservation = 'new_reservation';
	case UpdateReservation = 'update_reservation';
	case CancelReservation = 'cancel_reservation';
	case RegisterCustomer = 'register_customer';
}
