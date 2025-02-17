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
		Schema::table('clubs', function (Blueprint $table) {
			$table->boolean('invoice_autopay')->default(true);
			$table->integer('invoice_payment_time')->default(14);
			$table->boolean('invoice_last')->default(false);
		});

		Schema::table('club_product', function (Blueprint $table) {
			$table->integer('cost')->default(0);
		});

		Schema::table('club_game', function (Blueprint $table) {
			$table->boolean('include_on_invoice')->default(true);
			$table->json('include_on_invoice_status')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('clubs', function (Blueprint $table) {
			//
		});
	}
};
