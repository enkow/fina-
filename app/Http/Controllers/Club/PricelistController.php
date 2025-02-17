<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use App\Http\Requests\Club\AccessPricelistRequest;
use App\Http\Requests\Club\DestroyPricelistRequest;
use App\Http\Requests\Club\FilterPricelistsRequest;
use App\Http\Requests\Club\StorePricelistRequest;
use App\Http\Requests\Club\UpdatePricelistRequest;
use App\Http\Resources\GameResource;
use App\Http\Resources\PricelistResource;
use App\Models\Features\HasCustomViews;
use App\Models\Game;
use App\Models\Pricelist;
use App\Models\PricelistItem;
use App\Models\Translation;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PricelistController extends Controller
{
	private array $customViews = [];

	public function __construct()
	{
		$this->middleware(function (Request $request, Closure $next) {
			if (!club()->games->contains($request->route('game'))) {
				return redirect()->back();
			}

			$this->customViews = HasCustomViews::$defaultData['custom_views'];
			if ($request->route('game')?->hasFeature('has_custom_views')) {
				$this->customViews = $request
					->route('game')
					?->getFeaturesByType('has_custom_views')
					->first()->data['custom_views'];
			}

			$action = explode('@', $request->route()?->getActionName())[1];
			if (
				isset($this->customViews["pricelists.$action"]) &&
				$this->customViews["pricelists.$action"] === 'redirect:edit' &&
				in_array($action, ['index', 'create'])
			) {
				$pricelist = $request
					->route('game')
					?->pricelists()
					->where('club_id', clubId())
					->first();
				if (empty($pricelist) && $request->route('game')->hasFeature('has_only_one_pricelist')) {
					$pricelist = club()->createEmptyPricelist($request->route('game'));
				}
				if (session()->has('message')) {
					return redirect()
						->route('club.games.pricelists.edit', [
							'game' => $request->route('game'),
							'pricelist' => $pricelist,
						])
						->with('message', session()->get('message'));
				} else {
					return redirect()->route('club.games.pricelists.edit', [
						'game' => $request->route('game'),
						'pricelist' => $pricelist,
					]);
				}
			}

			return $next($request);
		});
	}

	public function index(FilterPricelistsRequest $request, Game $game): Response
	{
		$page = $this->customViews['pricelists.index'] ?? 'Club/Pricelists/Index';

		$pricelists = PricelistResource::collection(
			club()
				->pricelists()
				->where('game_id', $game->id)
				->paginate(request()['perPage']['pricelists'] ?? 10)
		);

		return Inertia::render($page, [
			'game' => new GameResource(
				Game::getCached()
					->where('id', $game->id)
					->first()
			),
			'pricelists' => $pricelists,
		]);
	}

	public function store(StorePricelistRequest $request, Game $game): RedirectResponse
	{
		$pricelist = club()
			->pricelists()
			->create(
				array_merge($request->only(['name']), [
					'game_id' => $game->id,
				])
			);

		foreach ($request->all()['days'] as $day => $items) {
			foreach ($items as $item) {
				$pricelist->pricelistItems()->create([
					'day' => $day,
					'from' => $item['from'],
					'to' => $item['to'],
					'price' => $item['price'],
				]);
			}
		}

		foreach ($request->all()['exceptions'] ?? [] as $pricelistException) {
			$pricelistExceptionModel = $pricelist->pricelistExceptions()->create($pricelistException);
			$pricelistExceptionModel->update([
				'start_at' => $pricelistException['start_at'],
				'end_at' => $pricelistException['end_at'],
			]);
		}

		return redirect()
			->route('club.games.pricelists.index', compact('game'))
			->with('message', [
				'type' => 'success',
				'content' => __('pricelist.successfully-stored'),
			]);
	}

	public function create(FilterPricelistsRequest $request, Game $game): Response
	{
		$page = $this->customViews['pricelists.create'] ?? 'Club/Pricelists/Create';

		return Inertia::render($page, [
			'game' => new GameResource($game),
		]);
	}

	public function edit(AccessPricelistRequest $request, Game $game, Pricelist $pricelist): Response
	{
		$page = $this->customViews['pricelists.edit'] ?? 'Club/Pricelists/Edit';

		return Inertia::render($page, [
			'game' => new GameResource(
				Game::getCached()
					->where('id', $game->id)
					->first()
			),
			'pricelist' => new PricelistResource($pricelist->load(['pricelistItems', 'pricelistExceptions'])),
		]);
	}

	public function update(
		UpdatePricelistRequest $request,
		Game $game,
		Pricelist $pricelist
	): RedirectResponse {
		$pricelist->update($request->only(['name']));

		$oldPricelistItemIds = $pricelist->pricelistItems()->pluck('id');

		foreach ($request->all()['days'] as $day => $items) {
			foreach ($items as $item) {
				$pricelist->pricelistItems()->create([
					'day' => $day,
					'from' => $item['from'],
					'to' => $item['to'],
					'price' => $item['price'],
				]);
			}
		}
		PricelistItem::whereIn('id', $oldPricelistItemIds)->delete();

		return redirect()
			->route('club.games.pricelists.index', [
				'game' => $game,
			])
			->with('message', [
				'type' => 'info',
				'content' => __('pricelist.successfully-updated'),
			]);
	}

	public function destroy(
		DestroyPricelistRequest $request,
		Game $game,
		Pricelist $pricelist
	): RedirectResponse {
		if ($pricelist->slots()->count()) {
			$gameTranslations = Translation::gamesTranslationsArray();

			return redirect()
				->route('club.games.pricelists.index', ['game' => $game])
				->with('message', [
					'type' => 'error',
					'content' => $gameTranslations[$game->id]['pricelist-destroy-has-slots-error'],
				]);
		}

		$pricelist->delete();

		return redirect()
			->route('club.games.pricelists.index', ['game' => $game])
			->with('message', [
				'type' => 'info',
				'content' => __('pricelist.successfully-destroyed'),
			]);
	}
}
