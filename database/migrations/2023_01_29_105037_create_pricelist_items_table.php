<?php

use App\Models\Pricelist;
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
		Schema::create('pricelist_items', function (Blueprint $table) {
			$table->id();
			$table->foreignIdFor(Pricelist::class);
			$table->tinyInteger('day');
			$table->time('from');
			$table->time('to');
			$table->integer('price');

			$table->softDeletes();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('pricelist_days');
	}
};
