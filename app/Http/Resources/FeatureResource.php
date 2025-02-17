<?php

namespace App\Http\Resources;

use App\Models\Country;
use App\Models\Customer;
use App\Models\Translation;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class FeatureResource extends JsonResource
{
    public array $withoutFields;

    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    /**
	 * Transform the resource into an array.
	 *
	 * @param  Request  $request
	 *
	 * @return array|Arrayable|JsonSerializable
	 */
	public function toArray($request): array
	{
		$countryId =
			club()->country_id ??
			(auth()->user()->country_id ??
				(request()->get('customer_id')
					? Customer::find(request()->get('customer_id'))?->country?->id
					: 170));

		return [
			'id' => $this->id,
			'type' => $this->type,
			'code' => $this->code,
			'data' => $this->data,
			'isTaggableIfGameReservationExist' => $this->isTaggableIfGameReservationExist,
			'game' => new GameResource($this->whenLoaded('game'), ['icon']),
            'translations' => Translation::retrieveFeatureTranslations(
                feature: $this,
                country: Country::getCountry($countryId)
            ),
			'pivot' => $this->pivot ?? null,
		];
	}
}
