<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class SlotResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  Request  $request
	 *
	 * @return array|Arrayable|JsonSerializable
	 */
	public function toArray($request): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'active' => $this->active,
			'pricelist_id' => $this->pricelist_id,
			'pricelist' => new PricelistResource($this->whenLoaded('pricelist')),
			//            'reservations' => $this->when((bool)auth()->user(),
			//                ReservationResource::collection($this->reservations)),
			'features' => FeatureResource::collection($this->whenLoaded('features')),
			'slot_id' => $this->slot_id,
			'childrenSlots' => self::collection($this->whenLoaded('childrenSlots')),
			'parentSlot' => new SlotResource($this->whenLoaded('parentSlot')),

			$this->mergeWhen(!request()->routeIs('reservations.show') && isset($this->bulb_status), [
                'bulb_status' => $this->bulb_status
            ]),

			'created_at' => $this->when(
				auth()
					->user()
					?->isType(['admin', 'manager']),
				$this->created_at
			),
		];
	}
}
