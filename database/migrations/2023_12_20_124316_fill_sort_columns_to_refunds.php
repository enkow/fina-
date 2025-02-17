<?php

use App\Models\Refund;
use App\Models\ReservationNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
				foreach ($refunds as $refund) {
					$refund->refreshHelperColumns();
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
