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
		Schema::create('countries', function (Blueprint $table) {
			$table->id();
			$table->boolean('active')->default(false);
			$table->string('payment_method_type')->nullable();
			$table->string('code', 3);
			$table->string('currency', 10)->default('EUR');
			$table->string('language', 10)->default('en');
			$table->string('timezone', 100)->default('Europe/Warsaw');
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
		Schema::dropIfExists('countries');
	}
};
