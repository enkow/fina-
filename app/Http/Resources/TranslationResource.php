<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class TranslationResource extends JsonResource
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
			'key' => $this->key,
			'value' => $this->value,

			$this->mergeWhen(
				auth()
					->user()
					?->isType('admin'),
				[
					'id' => $this->id,
					'country' => new CountryResource($this->whenLoaded('country')),
					'feature' => new FeatureResource($this->whenLoaded('feature')),
				]
			),
		];
	}
}
