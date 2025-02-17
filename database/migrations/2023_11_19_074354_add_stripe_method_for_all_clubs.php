<?php

use App\Enums\OnlinePayments;
use App\Models\Club;
use App\Models\PaymentMethods\Stripe;
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
		$clubs = Club::all();

		foreach ($clubs as $club) {
			if ($club->online_payments_enabled !== OnlinePayments::Disabled) {
				if ($club->paymentMethods()->count() === 0 || !$club->paymentMethod) {
					Stripe::enable($club);
				}
			}
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}
};
