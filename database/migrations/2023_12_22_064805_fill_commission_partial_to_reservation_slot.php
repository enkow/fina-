<?php

use App\Models\Refund;
use App\Models\ReservationNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Refund::whereNotNull('id')
			->with('firstReservationSlot.reservation.reservationNumber')
			->chunk(50, function ($refunds) {
				$reservationNumbers = ReservationNumber::select(
					'reservation_numbers.*',
					'reservation_slots.refund_id',
					DB::raw('CONCAT(customers.first_name, " ", customers.last_name) as customer_name')
				)
					->join(
						'reservation_slots',
						'reservation_numbers.numerable_id',
						'=',
						'reservation_slots.id'
					)
					->join('reservations', 'reservation_slots.reservation_id', '=', 'reservations.id')
					->join('customers', 'reservations.customer_id', '=', 'customers.id')
					->whereIn('reservation_slots.refund_id', $refunds->pluck('id')->toArray())
					->get();

				foreach ($refunds as $refund) {
					$refund->refreshHelperColumns(
						$reservationNumbers->where('refund_id', $refund->id)->toArray()
					);
					echo $refund->id . "\n";
				}
			});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('refunds', function (Blueprint $table) {
			//
		});
	}
};
