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
		Schema::table('club_game', function (Blueprint $table) {
			$table
				->decimal('fee_percent', 5, 2)
				->default(0)
				->change();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('club_game', function (Blueprint $table) {
			$table
				->integer('fee_percent')
				->default(0)
				->change();
		});
	}
};
