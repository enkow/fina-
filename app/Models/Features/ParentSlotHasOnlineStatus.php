<?php

namespace App\Models\Features;

use App\Models\Feature;
use App\Models\Slot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use JsonException;
use Parental\HasParent;

class ParentSlotHasOnlineStatus extends Feature
{
	use HasParent;

	public static string $name = 'parent_slot_has_online_status';
	public static array $defaultSlotData = ['status' => true];
	public array $defaultTranslations = [
		'active-online' => 'Dostępna online',
	];
	public array $conflictedFeatures = [ParentSlotHasOnlineStatus::class];

	/**
	 * @throws JsonException
	 */
	public function updateSlotData(Slot $slot, array $data = []): bool
	{
		$featureData = $data['features'][$this->id];
		$this->slots()->detach($slot);
		$this->slots()->attach($slot, [
			'data' => json_encode(
				[
					'status' => $featureData['status'],
				],
				JSON_THROW_ON_ERROR
			),
		]);

		return true;
	}

	public function slotQueryScope(Builder $query, array $data): Builder
	{
		if (clubId()) {
			return $query;
		}

		return $query->whereHas('parentSlot', function ($query) {
			$query->whereHas('features', function ($query) {
				$query->where('features.id', $this->id);
				$query->where('feature_payload.data->status', true);
			});
		});
	}
}
