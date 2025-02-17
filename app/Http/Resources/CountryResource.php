<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class CountryResource extends JsonResource
{
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
			'code' => $this->code,
			'currency' => $this->currency,
			'locale' => $this->locale,
			'timezone' => $this->timezone,
			'dialing_code' => $this->dialing_code,

			'clubs_count' => $this->whenCounted('clubs'),

			'paymentMethod' => new PaymentMethodResource($this->whenLoaded('paymentMethod')),
			'translations' => TranslationResource::collection($this->whenLoaded('translations')),
			'clubs' => ClubResource::collection($this->whenLoaded('clubs')),

			$this->mergeWhen(
				auth()
					->user()
					?->isType('admin'),
				[
					'id' => $this->id,
					'active' => $this->active,
				]
			),
		];
	}
}
