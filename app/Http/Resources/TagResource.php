<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class TagResource extends JsonResource
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
			$this->mergeWhen((bool) auth()->user(), [
				'id' => $this->id,
				'name' => $this->name,
			]),

			$this->mergeWhen(
				auth()
					->user()
					?->isType(['admin', 'manager']),
				[
					'customers' => CustomerResource::collection($this->whenLoaded('customers')),
				]
			),
		];
	}
}
