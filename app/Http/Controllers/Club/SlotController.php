<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use App\Http\Requests\Club\SearchSlotRequest;
use App\Http\Resources\GameResource;
use App\Http\Resources\PricelistResource;
use App\Http\Resources\SlotResource;
use App\Models\Features\FixedReservationDuration;
use App\Models\Features\HasCustomViews;
use App\Models\Features\ParentSlotHasOnlineStatus;
use App\Models\Game;
use App\Models\Pricelist;
use App\Models\Setting;
use App\Models\Slot;
use App\Models\Translation;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use JsonException;

class SlotController extends Controller
{
	private array $customViews = [];

	public function __construct()
	{
		$this->middleware(function (Request $request, Closure $next) {
			if (!club()->games->contains($request->route('game'))) {
				return redirect()->back();
			}

			$this->customViews = HasCustomViews::$defaultData['custom_views'];
			if ($request->route('game')->hasFeature('has_custom_views')) {
				$this->customViews = $request
					->route('game')
					?->getFeaturesByType('has_custom_views')
					->first()->data['custom_views'];
			}

			return $next($request);
		});

		$this->middleware(function (Request $request, Closure $next) {
			if (
				$request->route('slot')->pricelist->game_id !== $request->route('game')->id ||
				$request->route('slot')->pricelist->club_id !== clubId()
			) {
				return redirect()->back();
			}

			return $next($request);
		})->only(['edit', 'update', 'destroy', 'toggleActive', 'toggleOnlineStatus']);

		$this->middleware(function (Request $request, Closure $next) {
			if (
				!auth()
					->user()
					->isType(['admin', 'manager'])
			) {
				return redirect()->back();
			}

			return $next($request);
		})->only(['update', 'destroy', 'toggleActive', 'toggleOnlineStatus']);

		$this->middleware(function (Request $request, Closure $next) {
			if (
				!auth()
					->user()
					->isType(['admin', 'manager'])
			) {
				return redirect()->back();
			}
			$pricelist = $request
				->route('game')
				?->pricelists()
				->where('club_id', clubId())
				->first();
			if (empty($pricelist) && $request->route('game')?->hasFeature('has_only_one_pricelist')) {
				$pricelist = club()->createEmptyPricelist($request->route('game'));
			}

			return $next($request);
		})->only(['create', 'store']);
	}

	public function index(Game $game): Response
	{
		$page = $this->customViews['slots.index'] ?? 'Club/Slots/Index';

		return Inertia::render($page, [
			'game' => new GameResource(
				Game::getCached()
					->where('id', $game->id)
					->first()
			),
			'pricelists' => PricelistResource::collection(
				$game
					->pricelists()
					->where('club_id', clubId())
					->get()
			),
			'slots' => SlotResource::collection(
				$game
					->slots()
					->whereHas('pricelist', function ($query) {
						$query->where('club_id', clubId());
					})
					->whereNull('slot_id')
					->with('pricelist', 'childrenSlots.features', 'features')
					->orderBy('id')
					->paginate(request()['perPage']['slots_' . $game->id] ?? 10)
			),
		]);
	}

	public function store(Request $request, Game $game): RedirectResponse
	{
		$this->processValidation($request, $game);
		$slot = Pricelist::find(request()->get('pricelist_id'))
			->slots()
			->create([
				'name' => $request->get('name'),
			]);
		foreach ($game->features as $feature) {
			$feature->updateSlotData($slot, $request->all());
		}

		$gameTranslations = Translation::retrieve(countryId: club()->country_id, gameId: $game->id);

		return redirect()
			->route('club.games.slots.index', compact(['game']))
			->with('message', [
				'type' => 'info',
				'content' => $gameTranslations['successfully-stored'],
			]);
	}

	public function processValidation(Request $request, Game $game): void
	{
		$data = $request->all();
		$slotWithTheSameName = Slot::where('name', $request->get('name', ''))
			->whereHas('pricelist', function ($query) use ($game) {
				$query->where('game_id', $game->id);
				$query->where('club_id', clubId());
			})
            ->when($game->hasFeature('slot_has_parent'), function ($query) {
                $query->whereNull('slot_id');
            })
			->when(request()->route('slot'), function ($query) {
				$query->where('slot_id', request()->route('slot')->slot_id);
				$query->where('id', '!=', request()->route('slot')->id);
			})
			->when(request()->route('slot'), function ($query) {
				$query->whereNull('slot_id');
			})
			->first();
		$nameValidationRulesString = 'required|min:1|max:255';
		if (!empty($slotWithTheSameName)) {
			$nameValidationRulesString .= '|not_in:' . $slotWithTheSameName->name;
		}
		$validationArray = [
			'name' => $nameValidationRulesString,
			'pricelist_id' => 'required|exists:pricelists,id',
		];
		$validationNiceNames = [];
		foreach ($game->features as $feature) {
			$validationArray += $feature->getSlotDataValidationRules();
			$data['features'][$feature->id] = $feature->prepareSlotDataForValidation(
				$data['features'][$feature->id] ?? []
			);
			$validationNiceNames += $feature->getSlotDataValidationNiceNames();
		}
		$request->merge($data);
		Validator::make(
			$request->all(),
			$validationArray,
			[
				'name.not_in' => __('slot.the-given-name-is-already-taken'),
			],
			$validationNiceNames
		)->validate();
	}

	public function create(Game $game): mixed
	{
		$page = $this->customViews['slots.create'] ?? 'Club/Slots/Create';

		return Inertia::render($page, [
			'game' => new GameResource($game->load('features')),
			'pricelists' => PricelistResource::collection(
				club()
					->pricelists()
					->where('game_id', $game->id)
					->get()
			),
		]);
	}

	public function edit(Game $game, Slot $slot): Response
	{
		$page = $this->customViews['slots.edit'] ?? 'Club/Slots/Edit';

		return Inertia::render($page, [
			'game' => new GameResource($game->load('features')),
			'slot' => new SlotResource($slot->load('features', 'childrenSlots.features')),
			'pricelists' => PricelistResource::collection(
				club()
					->pricelists()
					->where('game_id', $game->id)
					->get()
			),
		]);
	}

	public function destroy(Game $game, Slot $slot): RedirectResponse
	{
		if ($slot->hasFutureReservationSlots(true, false)) {
			$gameTranslations = Translation::gamesTranslationsArray();

			return redirect()
				->route('club.games.slots.index', ['game' => $game])
				->with('message', [
					'type' => 'error',
					'content' => $gameTranslations[$game->id]['slot-destroy-has-reservations-error'],
				]);
		}

		$slot->delete();

		$gameTranslations = Translation::retrieve(countryId: club()->country_id, gameId: $game->id);

		return redirect()
			->route('club.games.slots.index', compact(['game']))
			->with('message', [
				'type' => 'info',
				'content' => $gameTranslations['successfully-updated'],
			]);
	}

	public function toggleActive(Game $game, Slot $slot): RedirectResponse
	{
		if ($slot->active === true && $slot->hasFutureReservationSlots(true, false)) {
			$gameTranslations = Translation::gamesTranslationsArray();

			return redirect()
				->route('club.games.slots.index', ['game' => $game])
				->with('message', [
					'type' => 'error',
					'content' => $gameTranslations[$game->id]['slot-unactive-has-reservations-error'],
				]);
		}
		$slot->update([
			'active' => !$slot->active,
		]);
		$gameTranslations = Translation::retrieve(countryId: club()->country_id, gameId: $game->id);
		club()->flushCache();

		return redirect()
			->back()
			->with('message', [
				'type' => 'info',
				'content' => $gameTranslations['successfully-updated'],
			]);
	}

	public function update(Request $request, Game $game, Slot $slot): RedirectResponse
	{
		$this->processValidation($request, $game);
		$slot->update($request->only(['name', 'pricelist_id']));
		foreach ($game->features as $feature) {
			$feature->updateSlotData($slot, $request->all());
		}

		$gameTranslations = Translation::retrieve(countryId: club()->country_id, gameId: $game->id);

		return redirect()
			->route('club.games.slots.index', [
				'game' => $game,
			])
			->with('message', [
				'type' => 'info',
				'content' => $gameTranslations['successfully-updated'],
			]);
	}

	public function search(Game $game, SearchSlotRequest $request): JsonResponse
	{
		return response()->json(
			Slot::getAvailable(
				array_merge(
					[
						'game_id' => $game->id,
						'club_id' => clubId(),
						'active' => true,
						'vacant' => true,
						'parent_slot_id' => $game->hasFeature('person_as_slot') ? null : 0,
					],
					$request->all()
				)
			)
		);
	}

	/**
	 * @throws JsonException
	 */
	public function toggleOnlineStatus(Game $game, Slot $slot): RedirectResponse
	{
		if (!$game->hasFeature('parent_slot_has_online_status') || $slot->slot_id !== null) {
			return redirect()->back();
		}
		$gameFeature = $game
			->features()
			->where('type', 'parent_slot_has_online_status')
			->first();
		$slotFeature = $slot
			->features()
			->where('type', 'parent_slot_has_online_status')
			->first();
		$slotFeaturePivotData =
			$slotFeature?->pivot->data ??
			json_encode(ParentSlotHasOnlineStatus::$defaultSlotData, JSON_THROW_ON_ERROR);
		$slotFeaturePivotDataParsed = json_decode($slotFeaturePivotData, true, 512, JSON_THROW_ON_ERROR);
		$gameFeature?->updateSlotData($slot, [
			'features' => [
				$gameFeature->id => [
					'status' => !$slotFeaturePivotDataParsed['status'],
				],
			],
		]);
		club()->flushCache();

		return redirect()->back();
	}
}
