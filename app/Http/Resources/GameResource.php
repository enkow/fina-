<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class GameResource extends JsonResource
{
    public array $withoutFields;

    public function __construct($resource, $withoutFields = [])
    {
        parent::__construct($resource);
        $this->withoutFields = is_array($withoutFields) ? $withoutFields : [];
    }

	/**
	 * Transform the resource into an array.
	 *
	 * @param  Request  $request
	 *
	 * @return array|Arrayable|JsonSerializable
	 */
	public function toArray($request)
	{
		return [
			'id' => $this->id,
			'parent' => $this->when($this->game_id, $this->game),
			'name' => $this->name,
            $this->mergeWhen(!in_array('icon', $this->withoutFields, true) && !request()->routeIs('reservations.show'), [
                'icon' => $this->icon,
            ]),
			'setting_icon_color' => $this->setting_icon_color,
			'pivot' => $this->pivot,
			'photo' => $this->photo,
			'created_at' => $this->when(
				auth()
					->user()
					?->isType('admin'),
				$this->created_at
			),

			'clubs_count' => $this->whenCounted('clubs'),
			'reservations_count' => $this->whenCounted('reservations'),

			'features' => FeatureResource::collection($this->whenLoaded('features')),
			'slots' => SlotResource::collection($this->whenLoaded('slots')),
			'discountCodes' => DiscountCodeResource::collection($this->whenLoaded('discountCodes')),
			'specialOffers' => SpecialOfferResource::collection($this->whenLoaded('specialOffers')),
			'translations' => TranslationResource::collection($this->whenLoaded('translations')),
			'bulbs' => BulbResource::collection($this->whenLoaded('bulbAdapters')),
		];
	}
}
