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
		\Illuminate\Support\Facades\DB::table('clubs')->update([
			'widget_locales' => \Illuminate\Support\Facades\DB::raw('widget_languages'),
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('widget_languages_to_widget_locales_in_clubs', function (Blueprint $table) {
			//
		});
	}
};
