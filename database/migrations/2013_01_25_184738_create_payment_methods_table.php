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
		Schema::create('payment_methods', function (Blueprint $table) {
			$table->id();
			$table
				->string('code', 20)
				->unique()
				->nullable();
			$table->string('type')->nullable();
			$table
				->foreignIdFor(\App\Models\Club::class)
				->nullable()
				->constrained();
			$table->boolean('activated')->default(false);
			$table->boolean('online');
			$table
				->string('external_id')
				->nullable()
				->index();
			$table->binary('credentials')->nullable();
			$table->softDeletes();
			$table->timestamps();

			$table->boolean('exists')->virtualAs('IF(`deleted_at` IS NULL, true, NULL)');
			$table->unique(['club_id', 'exists']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('payment_methods');
	}
};
