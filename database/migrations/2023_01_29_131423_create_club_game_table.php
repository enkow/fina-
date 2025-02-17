<?php

use App\Models\Club;
use App\Models\Game;
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
		Schema::create('club_game', function (Blueprint $table) {
			$table->id();
			$table->foreignIdFor(Club::class);
			$table->foreignIdFor(Game::class);
			$table->json('custom_names');
			$table->integer('weight')->default(1);
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
		Schema::dropIfExists('club_game');
	}
};
