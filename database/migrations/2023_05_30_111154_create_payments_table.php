<?php

use App\Enums\PaymentStatus;
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
		Schema::create('payments', function (Blueprint $table) {
			$table->id();
			$table->morphs('payable');
			$table->foreignIdFor(PaymentMethod::class)->constrained();
			$table->string('status')->default(PaymentStatus::Pending->value);

			$table->integer('total');
			$table->integer('commission');
			$table->string('currency', 3);

			$table->string('external_id')->nullable();

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
		Schema::dropIfExists('payments');
	}
};
