<?php

use App\Models\Customer;
use App\Models\Tag;
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
		Schema::create('customer_tag', function (Blueprint $table) {
			$table->id();
			$table->foreignIdFor(Customer::class)->constrained();
			$table->foreignIdFor(Tag::class)->constrained();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('customers_tags');
	}
};
