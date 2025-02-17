<?php

return [
	'slots.index' => ['Club/Slots/Custom/UnnumberedTables/Index', 'Club/Slots/Custom/NumberedTables/Index'],
	'slots.edit' => [
		'Club/Slots/Custom/Bowling/Edit',
		'Club/Slots/Custom/UnnumberedTables/Edit',
		'Club/Slots/Custom/NumberedTables/Edit',
	],
	'slots.create' => [
		'Club/Slots/Custom/Bowling/Create',
		'Club/Slots/Custom/UnnumberedTables/Create',
		'Club/Slots/Custom/NumberedTables/Create',
	],
	'pricelists.edit' => [
		'Club/Pricelists/Custom/UnnumberedTables/Edit',
		'Club/Pricelists/Custom/NumberedTables/Edit',
	],
	'pricelists.index' => ['redirect:edit'],
	'pricelists.create' => ['redirect:edit'],
	'reservations.calendar' => [
		'Club/Reservations/Custom/NumberedTables/Calendar',
		'Club/Reservations/Custom/UnnumberedTables/Calendar',
	],
	'reservations.create-form' => [
		'Club/Reservations/CustomReservationForm/Billiard/Create',
		'Club/Reservations/CustomReservationForm/Bowling/Create',
	],
	'reservations.edit-form' => [
		'Club/Reservations/CustomReservationForm/Billiard/Edit',
		'Club/Reservations/CustomReservationForm/Bowling/Edit',
	],
];
