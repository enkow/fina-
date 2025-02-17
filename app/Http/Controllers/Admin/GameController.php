<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGameRequest;
use App\Http\Requests\Admin\UpdateGameRequest;
use App\Http\Resources\FeatureResource;
use App\Http\Resources\GameResource;
use App\Models\Feature;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GameController extends Controller
{
	public function index(): Response
	{
		$games = GameResource::collection(Game::withCount('clubs')->paginate(10));

		return Inertia::render('Admin/Games/Index', compact(['games']));
	}

	public function store(StoreGameRequest $request): RedirectResponse
	{
		$storeData = $request->only(['name', 'description', 'icon', 'setting_icon_color']);

		if ($request->hasFile('photo')) {
			$storeData['photo'] = $request->file('photo')?->storePublicly('/', ['disk' => 'gameImages']);
		}

		Game::create($storeData);

		return redirect()
			->route('admin.games.index')
			->with('message', [
				'type' => 'success',
				'content' => 'Dodano grę',
			]);
	}

	public function create(): Response
	{
		return Inertia::render('Admin/Games/Create', [
			'games' => GameResource::collection(Game::all()),
		]);
	}

	public function edit(Game $game): Response
	{
		$featureTypes = array_keys((new Feature())->getChildTypes());
		sort($featureTypes);
		return Inertia::render('Admin/Games/Edit', [
			'games' => GameResource::collection(Game::where('id', '!=', $game->id)->get()),
			'game' => new GameResource($game->loadCount('reservations')),
			'featureTypes' => $featureTypes,
			'features' => FeatureResource::collection($game->features),
			'gameCustomViews' => config('game-custom-views'),
		]);
	}

	public function update(UpdateGameRequest $request, Game $game): RedirectResponse
	{
		$editData = $request->only(['name', 'description', 'icon', 'setting_icon_color']);

		if ($request->hasFile('photo')) {
			$editData['photo'] = $request->file('photo')?->storePublicly('/', ['disk' => 'gameImages']);
		}

		$game->update($editData);

		return redirect()
			->route('admin.games.index')
			->with('message', [
				'type' => 'info',
				'content' => 'Zaktualizowano grę',
			]);
	}

	public function destroy(Game $game): RedirectResponse
	{
		$game->delete();

		return redirect()
			->route('admin.games.index')
			->with('message', [
				'type' => 'info',
				'message' => 'Usunięto grę',
			]);
	}
}
