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
		Schema::table('reservations', function (Blueprint $table) {
			$table->boolean('show_customer_note_on_calendar')->after('customer_note');
			$table->boolean('show_club_note_on_calendar')->after('club_note');
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
			//
		});
	}
};
