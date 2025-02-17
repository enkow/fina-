<?php

namespace App\Observers;

use App\Models\Game;
use Illuminate\Support\Facades\Cache;

class GameObserver
{
	public function created(Game $game): void
	{
		Cache::flush();
	}

	public function updated(Game $game): void
	{
		Cache::flush();
	}

	public function deleted(Game $game): void
	{
		foreach ($game->features as $feature) {
			$feature->settings()->delete();
		}
		$game->features()->delete();
		$game->translations()->delete();
	}
}
