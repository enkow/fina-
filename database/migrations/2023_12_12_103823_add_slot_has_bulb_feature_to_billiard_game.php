<?php

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
		$billiardGame = \App\Models\Game::where('name', 'billiard')->first();
		if (!empty($billiardGame)) {
			$billiardGame->features()->create([
				'type' => 'slot_has_bulb',
				'code' => 'slot_has_bulb',
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
		$billiardGame = \App\Models\Game::where('name', 'billiard')->first();
		if (!empty($billiardGame)) {
			$billiardGame
				->features()
				->where('type', 'slot_has_bulb')
				->where('code', 'slot_has_bulb')
				->delete();
		}
	}
};
