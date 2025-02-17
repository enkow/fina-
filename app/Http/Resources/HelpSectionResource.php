<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class HelpSectionResource extends JsonResource
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
			'id' => $this->id,
			'active' => $this->active,
			'title' => $this->title,
			'description' => $this->description,

			'helpItems' => HelpItemResource::collection($this->whenLoaded('helpItems')),

			$this->mergeWhen(
				auth()
					->user()
					?->isType('admin'),
				[
					'country' => new CountryResource($this->whenLoaded('country')),
					'weight' => $this->weight,
					'created_at' => $this->created_at,
				]
			),
		];
	}
}
