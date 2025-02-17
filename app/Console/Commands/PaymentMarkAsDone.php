<?php

namespace App\Console\Commands;

use App\Enums\ReservationSlotStatus;
use App\Events\ReservationStatusChanged;
use App\Events\ReservationStored;
use App\Models\ReservationNumber;
use App\Notifications\Customer\ReservationStoredNotification;
use Illuminate\Console\Command;
use Illuminate\Notifications\AnonymousNotifiable;

class PaymentMarkAsDone extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'payment:markAsDone';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		$reservationNumber = ReservationNumber::orderByDesc('id')->first();
		$reservation = $reservationNumber->numerable->reservation;
		$reservation->reservationSlots()->update([
			'status' => ReservationSlotStatus::Confirmed,
		]);
		event(new ReservationStatusChanged($reservation));
		event(new ReservationStored($reservation));
	}
}
