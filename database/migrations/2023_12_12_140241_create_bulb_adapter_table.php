<?php

use App\Models\PaymentMethod;
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
		Schema::create('bulb_adapters', function (Blueprint $table) {
			$table->id();
			$table->string('type')->nullable();
			$table
				->foreignIdFor(\App\Models\Setting::class)
				->nullable()
				->constrained();
			$table->boolean('synchronize')->default(false);
			$table->binary('credentials')->nullable();
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
		Schema::dropIfExists('bulb_adapters');
	}
};
