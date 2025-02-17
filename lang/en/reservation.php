<?php

return [
	'singular' => 'Reservations',
	'plural' => 'Reservations',
	'number' => 'Reservation number',
	'group-reservation' => 'Group reservation',
	'online-reservations' => 'Online reservations',
	'club-calendar' => 'Club calendar',
	'reservation-search' => 'Reservation search engine',
	'calendar-name' => 'Name on calendar',
	'start-date' => 'Date',
	'reservation-hours' => 'Reservation hours',
	'duration-time' => 'Duration',
	'reservation-type' => 'Reservation type',
	'special-offer' => 'Promotion',
	'set' => 'Addon',
	'discount-code' => 'Discount coupon',
	'payment-status' => 'Payment status',
	'club-reservation' => 'Club reservation',
	'anonymous-reservation' => 'Anonymous reservation',
	'club-note' => 'Club comment',
	'no-club-note' => 'No club comment',
	'customer-note' => 'Customer comment',
	'no-customer-note' => 'No customer comment',
	'send-sms-notification' => 'Send SMS notification',
	'send-email-notification' => 'Send Email notification',
	'add-reservation' => 'Add a reservation',
	'show-on-reservation' => 'Show on reservation',
	'cancel-reservation' => 'Cancel reservation',
	'related-reservations' => 'Related Reservations',
	'cancel-related-reservations' => 'Cancel related-reservations',
	'apply-to-all-reservations' => 'Apply to all reservations',
	'reduce-the-online-number-of-available-slots' => 'Reduce the number of available seats online',
	'number-of-slots' => 'Number of seats',
	'new-reservation' => 'New reservation',
	'edit-reservation' => 'Edit reservation',
	'view-reservation' => 'Show reservation',
	'new-price' => 'New price',
	'update-price' => 'Update price',
	'update-reservation' => 'Save',
	'reservations-found' => 'Reservation list',
	'update-reservation-confirm' => 'Are you sure you want to update the selected reservation?',
	'reservation-history' => 'Reservation history',
	'reservation-number-value' => 'Reservation number :value',
	'reservation-number' => 'reservation number',
	'reservation-numbers' => 'Reservation numbers',
	'reservations-list' => 'Reservation list',
	'cancelation-confirmation' => 'Are you sure you want to cancel your reservation?',
	'cancelation-reason' => 'Reason for cancellation',
	'canceled-reservations' => 'Cancelled reservations',
	'cancelation-reason-type-select-placeholder' => 'Select reason',
	'no-results' => 'No results',
	'none' => 'none',
	'canceler' => 'Canceler',
	'no-history-logged' => 'No saved changes',
	'place' => 'Place',
	'game-price' => 'Game-price',
	'reservation-datetime' => 'Reservation date/time',
	'all-reservations' => 'All reservations',
	'timer-status' => 'Timer',
	'add-reservation-to-google-calendar' => 'Add reservation to google calendar',
	'app-reservation-mail-title' => 'Reservation in :app_name',
	'calendar-reservation-title' => ':club_name,:game_name nr #:reservation_number',
	'calendar-reservation-details' => 'Reservation :game_name #:reservation_number at :club_name',
	'reservation-notification-sms-content' =>
		'We have saved your reservation at :club_name for :start_at. You are welcome!',
	'reservation-stored-notification-content' =>
		'New reservation! :game_name - :start_at :customer_name just made a reservation with the number: :number',
	'reservation-canceled' => 'Cancelled reservation!',
	'contact-with-club-mail' => 'Contact the club with any questions',
	'canceled-by' => 'Cancelled by',
	'reservation-hour' => 'Reservation time',
	'changed-by-value' => 'Changed by: :value',
	'created-by-value' => 'Created: :value (:datetime)',

	'statuses' => [
		0 => 'Unpaid',
		1 => 'Paid',
		2 => 'Expired',
	],

	'status' => [
		'unpaid' => 'unpaid',
		'offline' => 'offline',
		'paid-card' => 'paid by card',
		'paid-cash' => 'paid by cash',
		'paid-cashless' => 'paid cashless',
		'during-payment' => 'during payment',
		'paid-online' => 'paid online',
		'all' => 'all',
		'canceled-by-customer' => 'canceled by customer',
		'canceled-by-club' => 'canceled by club',
		'canceled-by-system' => 'canceled by system',
		'club-reservation' => 'club reservation',
		'club' => 'club',
		'online' => 'online',
		'paid' => 'paid',
		'pending' => 'pending',
	],

	'timer-statuses' => [
		'0' => 'inactive',
		'1' => 'off',
		'2' => 'on',
		'3' => 'paused',
		'4' => 'stopped',
	],

	'cancelation-types' => [
		'1' => 'Customer didn\'t show up',
		'2' => 'Customer canceled the reservation',
		'0' => 'Other reason',
	],

	'start-date-filters' => [
		'yesterday' => 'Yesterday',
		'today' => 'Today',
		'tomorrow' => 'Tomorrow',
		'last-seven-days' => 'Last-seven-days',
		'last-thirty-days' => 'Last thirty days',
		'this-month' => 'This month',
		'this-year' => 'This year',
	],

	'placeholders' => [
		'first-name' => 'John',
		'last-name' => 'Kowalski',
		'phone' => '000-000-000-000',
		'email' => 'john.kowalskis@qmail.com',
	],

	'rate-request-notification' => [
		'title' => 'Rate your reservation at :club_name',
		'action-text' => 'Rate',
		'intro-lines' => [
			'0' => 'Rate your visit in :club_name',
		],
	],
	'canceled-notification' => [
		'title' => 'Cancellation of reservation :reservation_number at :club_name',
		'greeting' =>
			'Your transaction could not be processed because it was not paid within the regulatory 5 minutes.',
		'outro-lines' => [
			'0' => 'The reservation was not created.',
			'1' =>
				'If you want to create a new reservation - start the reservation process from scratch and pay it within a maximum of 5 minutes.',
		],
		'refunded-info' => 'Funds for the reservation will be refunded',
		'not-refunded-info' => 'Funds for the reservation will not be refunded',
	],
	'rated-notification' => [
		'title' => 'Reservation evaluated :reservation_number',
	],

	'successfully-canceled-content' => 'The reservation was successfully canceled',
	'successfully-canceled-content-plural' => 'Reservations were successfully canceled',

	'successfully-stored-content' => 'Reservation was successfully created',
	'successfully-stored-content-plural' => 'Reservations were successfully created',

	'successfully-updated-content' => 'Reservation has been successfully updated',
	'successfully-updated-content-plural' => 'Reservations have been successfully updated',

	'successfully-stored-singular-short' => 'Reservation has been added!',
	'successfully-stored-plural-short' => 'Group reservation added!',

	'the-club-is-closed-during-these-hours' => 'The club is closed during these hours',
	'reservation-moved-to-different-pricelist-error' =>
		'You can only move paid reservations within the same price!',
	'reservation-moved-to-different-pricelist-timer-error' =>
		'You can only move reservations with an active timer within the same price!',
	'paid-reservation-duration-change-error' => 'Only unpaid reservations can be extended and shortened',
	'pending-reservation-modified-error' => 'Pending reservations cannot be edited',
	'reservation-time-changed-when-timer-enabled-error' =>
		'You cannot change the reservation time with the timer on',
	'reservation-timer-stopped' => 'The timer has been stopped, the reservation has been updated',

	'sms' => [
		'stored' =>
			'Confirmation of reservation no. :number for :game, :day o\'clock :time. Payment status :payment_status :price.',
		'updated' =>
			'Update reservation no. :number for :game, :day o\'clock :time. Payment status :payment_status :price.',
		'canceled' =>
			'Cancellation of reservation no. :number for :game, :day o\'clock :time. Payment status :payment_status :price.',
	],
];
