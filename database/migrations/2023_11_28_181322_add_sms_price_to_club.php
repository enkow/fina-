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
			$table->integer('sms_price_online')->default(0);
			$table->integer('sms_price_offline')->default(0);
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
			$table->dropColumn('sms_price_online');
			$table->dropColumn('sms_price_offline');
		});
	}
};
