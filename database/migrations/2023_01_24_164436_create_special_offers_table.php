<?php

use App\Models\Club;
use App\Models\Game;
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
		Schema::create('special_offers', function (Blueprint $table) {
			$table->id();
			$table->boolean('active')->default(true);
			$table->boolean('active_by_default')->default(false);
			$table->foreignIdFor(Club::class)->constrained();
			$table->foreignIdFor(Game::class)->constrained();
			$table->integer('value')->default(0);
			$table->string('name', 100);
			$table->string('description', 255)->nullable();
			$table->json('active_week_days');
			$table->string('time_range_type');
			$table->json('time_range')->nullable();
			$table->integer('duration')->nullable();
			$table->integer('slots')->nullable();

			$table->json('when_applies')->nullable();
			$table->boolean('applies_default')->default(true);

			$table->json('when_not_applies')->nullable();

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
		Schema::dropIfExists('special_offers');
	}
};
