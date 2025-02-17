<?php

namespace App\Models\Features;

use App\Http\Resources\SlotResource;
use App\Models\Club;
use App\Models\Feature;
use App\Models\Game;
use App\Models\Pricelist;
use App\Models\Setting;
use App\Models\Slot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Parental\HasParent;

class SlotHasParent extends Feature
{
	use HasParent;

	public static string $name = 'slot_has_parent';

	public array $defaultTranslations = [
		'widget-select-any' => 'Dowolna',
		'parent-slot' => 'Sala',
		'choose-parent-slot' => 'Wybierz salę',
		'parent-slot-name' => 'Nazwa sali',
		'actives' => 'Aktywnych',
	];

	public array $conflictedFeatures = [PersonAsSlot::class, SlotHasParent::class];
	public bool $isTaggableIfGameReservationExist = false;

	public function prepareDataForAction(array $data): array
	{
		$data['parent_slot_id'] =
			$data['parent_slot_id'] ?? ($data['features'][$this->id]['parent_slot_id'] ?? null);
		return $data;
	}

	public function prepareDataForSlotSearch($data): array
	{
		return $this->prepareDataForAction($data);
	}

	public function getSlotDataValidationNiceNames(): array
	{
		$result = [
			"features.$this->id.items.*.name" => __('main.number'),
		];
		$featureIds = array_keys(request()->all()['features'][$this->id]['items'][0]['features'] ?? []);
		$features = Feature::query()
			->whereIn('id', $featureIds)
			->get();
		foreach ($features as $feature) {
			$featureValidationKeyBase = "features.$this->id.items.*";
			$result[$featureValidationKeyBase . ".features.$feature->id"] = 'required|array';
			foreach ($feature->getSlotDataValidationNiceNames() as $key => $value) {
				$result[$featureValidationKeyBase . '.' . $key] = $value;
			}
		}
		return $result;
	}

	public function getSlotDataValidationRules(): array
	{
		$validationArray = [];
		if ($this->game->hasFeature('book_singular_slot_by_capacity')) {
			$validationArray = [
				"features.$this->id.items" => 'required|array',
				"features.$this->id.items.*" => 'required|array',
				"features.$this->id.items.*.uid" => 'required|integer',
				"features.$this->id.items.*.name" => 'required|string|distinct',
				"features.$this->id.items.*.features" => 'required|array',
			];
			$featureIds = array_keys(request()->get('features')[$this->id]['items'][0]['features']);
			$features = Feature::query()
				->whereIn('id', $featureIds)
				->get();
			foreach ($features as $feature) {
				$featureValidationKeyBase = "features.$this->id.items.*";
				$validationArray[$featureValidationKeyBase . ".features.$feature->id"] = 'required|array';
				foreach ($feature->getSlotDataValidationRules() as $key => $value) {
					$validationArray[$featureValidationKeyBase . '.' . $key] = $value;
				}
			}
		}

		return $validationArray;
	}

	public function updateSlotData(Slot $slot, array $data = []): bool
	{
		$featureData = $data['features'][$this->id];
		if (!isset($featureData['items'])) {
			return true;
		}

		$slot
			->childrenSlots()
			->whereNotIn('id', array_map(static fn($item) => $item['uid'], $featureData['items']))
			->delete();

		foreach ($featureData['items'] as $item) {
			//If the uid is negative, it means that we are serving the slot just added by the user.
			if ($item['uid'] < 0) {
				$childrenSlot = $slot->childrenSlots()->create([
					'pricelist_id' => $slot->pricelist_id,
					'name' => $item['name'],
				]);
			}
			//We remove all slots that were not submitted in the request - this means that they have been removed.
			elseif ($item['uid'] > 0) {
				$slot
					->childrenSlots()
					->where('id', $item['uid'])
					->update([
						'name' => $item['name'],
						'pricelist_id' => $slot->pricelist_id,
					]);
				$childrenSlot = Slot::find($item['uid']);
			} else {
				continue;
			}

			foreach (request()?->route('game')->features as $feature) {
				if (!isset($item['features'][$feature->id])) {
					continue;
				}
				$feature->updateSlotData($childrenSlot, $item);
			}
		}

		return true;
	}

	public function slotQueryScope(Builder $query, array $data): Builder
	{
		if (!($data['features'][$this->id]['parent_slot_id'] ?? false)) {
			return $query;
		}
		return $query->when($data['features'][$this->id]['parent_slot_id'], function ($query) use ($data) {
			$query->where('slot_id', $data['features'][$this->id]['parent_slot_id']);
		});
	}

	public function getWidgetData(array $data): array
	{
        $game = $this->game;
		$club = $data['club'];
        $pricelistIds = Cache::remember('game:'.$game->id.':club:'.$club->id.':pricelist_ids', 30, function () use ($game, $club) {
            return Pricelist::where('game_id', $game->id)
                ->where('club_id', $club->id)
                ->pluck('id')
                ->toArray();
        });
		return [
			'parentSlots' => SlotResource::collection(
				$club
					->slots()
					->whereNull('slot_id')
					->whereIn('pricelist_id', $pricelistIds)
					->where(function ($query) {
						$query->whereDoesntHave('features', function ($query) {
							$query->where('type', 'parent_slot_has_online_status');
						});
						$query->orWhereHas('features', function ($query) {
							$query->where('feature_payload.data->status', true);
						});
					})
					->get()
			),
		];
	}
}
