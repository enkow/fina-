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
		Schema::table('refunds', function (Blueprint $table) {
			$table->integer('reservation_number_sort')->nullable();
			$table->string('reservation_numbers_search')->nullable();
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
			$table->dropColumn('reservation_number_sort')->nullable();
			$table->dropColumn('reservation_numbers_search')->nullable();
		});
	}
};
