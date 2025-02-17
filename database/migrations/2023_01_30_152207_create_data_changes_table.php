<?php

use App\Models\Reservation;
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
		Schema::create('data_changes', function (Blueprint $table) {
			$table->id();
			$table->morphs('changable');
			$table->foreignIdFor(\App\Models\User::class, 'triggerer_id')->nullable();
			$table->json('old');
			$table->json('new');
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
		Schema::dropIfExists('reservation_changes');
	}
};
