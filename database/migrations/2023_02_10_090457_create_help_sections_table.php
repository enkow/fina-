<?php

use App\Models\Country;
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
		Schema::create('help_sections', function (Blueprint $table) {
			$table->id();
			$table->boolean('active')->default(true);
			$table->foreignIdFor(Country::class);
			$table->string('title', 255);
			$table->string('description', 4000);
			$table->integer('weight')->default(1);
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
		Schema::dropIfExists('help_sections');
	}
};
