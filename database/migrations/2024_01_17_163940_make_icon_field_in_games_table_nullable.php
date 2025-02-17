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
		Schema::table('games', function (Blueprint $table) {
			$table
				->text('icon', 10000)
				->nullable()
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
		Schema::table('games', function (Blueprint $table) {
			$table
				->text('icon', 10000)
				->nullable(false)
				->change();
		});
	}
};
