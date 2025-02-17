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
		Schema::create('reservation_slot_timer_entries', function (Blueprint $table) {
			$table->id();
			$table->foreignIdFor(\App\Models\ReservationSlot::class);
			$table->dateTime('start_at');
			$table->dateTime('end_at')->nullable();
			$table->boolean('stopped')->default(false);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('reservation_slot_timers');
	}
};
