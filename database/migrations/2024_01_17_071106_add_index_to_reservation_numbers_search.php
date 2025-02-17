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
		Schema::table('refunds', function (Blueprint $table) {
			$table->index('reservation_numbers_search');
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
			$table->dropIndex('reservation_numbers_search');
		});
	}
};
