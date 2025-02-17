<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class BulbResource extends JsonResource
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
			'active' => $this->active,
			'synchronize' => $this->synchronize,
			'credentials' => $this->credentials,
			'type' => $this->type,
		];
	}
}
