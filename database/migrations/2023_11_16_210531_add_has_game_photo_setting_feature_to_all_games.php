<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Game;
use App\Models\Feature;

return new class extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		foreach (Game::all() as $game) {
			$game->features()->create([
				'type' => 'has_game_photo_setting',
				'code' => 'has_game_photo_setting',
				'data' => [],
			]);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Feature::where('code', 'has_game_photo_setting')->delete();
	}
};
