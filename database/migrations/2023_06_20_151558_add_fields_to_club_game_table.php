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
				->boolean('enabled_on_widget')
				->after('weight')
				->default(true);
			$table
				->integer('fee_percent')
				->after('enabled_on_widget')
				->default(0);
			$table
				->integer('fee_fixed')
				->after('fee_percent')
				->default(0);
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
			//
		});
	}
};
