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
		Schema::create('slots', function (Blueprint $table) {
			$table->id();
			$table->foreignIdFor(\App\Models\Slot::class)->nullable();
			$table->string('name')->nullable();
			$table->boolean('active')->default(true);
			$table->foreignIdFor(Pricelist::class)->constrained();
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
		Schema::dropIfExists('slots');
	}
};
