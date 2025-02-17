<?php

use App\Models\Club;
use App\Models\User;
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
		Schema::create('sets', function (Blueprint $table) {
			$table->id();
			$table->boolean('active')->default(true);
			$table->foreignIdFor(Club::class)->constrained();
			$table->string('name', 100);
			$table->string('photo', 255)->nullable();
			$table->text('description', 1000);
			$table->integer('price');
			$table->json('quantity');
			$table->foreignIdFor(User::class, 'creator_id')->nullable();
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
		Schema::dropIfExists('sets');
	}
};
