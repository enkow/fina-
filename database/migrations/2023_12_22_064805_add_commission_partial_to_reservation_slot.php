<?php

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
		Schema::table('reservation_slots', function (Blueprint $table) {
			$table->integer('app_commission_partial')->nullable();
			$table->integer('club_commission_partial')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('reservation_slots', function (Blueprint $table) {
			$table->dropColumn('app_commission_partial');
			$table->dropColumn('club_commission_partial');
		});
	}
};
