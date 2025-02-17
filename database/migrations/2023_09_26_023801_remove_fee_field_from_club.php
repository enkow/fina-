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
			$table->dropColumn('monthly_subscription_amount');
			$table->dropColumn('yearly_subscription_amount');
		});

		Schema::table('invoices', function (Blueprint $table) {
			$table->dropColumn('fee_year');
			$table->dropColumn('fee_month');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
	}
};
