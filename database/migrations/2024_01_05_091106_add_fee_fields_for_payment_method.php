<?php

use App\Models\ReservationSlot;
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
		Schema::table('payment_methods', function (Blueprint $table) {
			$table->integer('fee_percentage')->default(0);
			$table->integer('fee_fixed')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('payment_methods', function (Blueprint $table) {
			$table->dropColumn('fee_percentage');
			$table->dropColumn('fee_fixed');
		});
	}
};
