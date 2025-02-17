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
		Schema::create('bulb_actions', function (Blueprint $table) {
			$table->id();
			$table
				->foreignIdFor(\App\Models\Slot::class)
				->nullable()
				->constrained();
			$table->tinyInteger('bulb_status');
			$table->text('reason', 16);
			$table->nullableMorphs('assigned_to');
			$table->timestamp('run_at')->nullable();
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
		Schema::dropIfExists('bulb_actions');
	}
};
