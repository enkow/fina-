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
		Schema::table('invoices', function (Blueprint $table) {
			$table->string('title')->nullable();
			$table->string('lang', 2)->default('en');
			$table->bigInteger('fakturownia_id')->nullable();
			$table->integer('vat');
			$table->boolean('auto_send');
			$table->boolean('advance_payment');
			$table->integer('fee_year');
			$table->integer('fee_month');
			$table->integer('days_for_payment');
			$table->boolean('last')->defaul(false);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('invoices', function (Blueprint $table) {
			//
		});
	}
};
