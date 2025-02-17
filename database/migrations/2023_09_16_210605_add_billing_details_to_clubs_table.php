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
			$table
				->string('billing_name', 100)
				->after('city')
				->nullable();
			$table
				->string('billing_address', 100)
				->after('billing_name')
				->nullable();
			$table
				->string('billing_postal_code', 20)
				->after('billing_address')
				->nullable();
			$table
				->string('billing_city', 100)
				->after('billing_postal_code')
				->nullable();
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
