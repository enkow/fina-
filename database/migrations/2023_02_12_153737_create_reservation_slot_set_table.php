<?php

use App\Models\ReservationSlot;
use App\Models\Set;
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
		Schema::create('reservation_slot_set', function (Blueprint $table) {
			$table->id();
			$table->foreignIdFor(ReservationSlot::class)->constrained();
			$table->foreignIdFor(Set::class)->constrained();
			$table->integer('price')->default(null);
			$table->softDeletes();
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
		Schema::dropIfExists('reservation_slot_set');
	}
};
