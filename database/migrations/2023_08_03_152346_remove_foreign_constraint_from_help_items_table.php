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
		Schema::table('help_items', function (Blueprint $table) {
			$table->dropForeign('help_items_help_section_id_foreign');
			$table->dropIndex('help_items_help_section_id_foreign');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('help_items', function (Blueprint $table) {
			//
		});
	}
};
