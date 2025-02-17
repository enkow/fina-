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
			$table->boolean('preview_mode')->default(false);
			$table->boolean('invoice_autosend')->default(false);
			$table->boolean('invoice_advance_payment')->default(true);
			$table->date('invoice_next_month')->nullable();
			$table->date('invoice_next_year')->nullable();
			$table
				->string('invoice_lang', 2)
				->nullable()
				->default('en');
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
