<?php

use App\Models\Club;
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
		Schema::create('opening_hours', function (Blueprint $table) {
			$table->id();
			$table->foreignIdFor(Club::class)->constrained();

			$table->integer('day');

			$table->time('club_start');
			$table->time('club_end');
			$table->boolean('club_closed')->default(false);
			$table->boolean('open_to_last_customer')->default(false);

			$table->time('reservation_start');
			$table->time('reservation_end');
			$table->boolean('reservation_closed')->default(false);

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
		Schema::dropIfExists('opening_hours');
	}
};
