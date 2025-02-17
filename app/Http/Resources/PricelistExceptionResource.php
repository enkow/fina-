<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class PricelistExceptionResource extends JsonResource
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
			'start_at' => $this->start_at,
			'end_at' => $this->end_at,
			'from' =>
				Carbon::parse($this->from)->format('H:i') === '23:59'
					? '24:00'
					: Carbon::parse($this->from)->format('H:i'),
			'to' =>
				Carbon::parse($this->to)->format('H:i') === '23:59'
					? '24:00'
					: Carbon::parse($this->to)->format('H:i'),
			'price' => $this->price,
			'pricelist' => new PricelistResource($this->whenLoaded('pricelist')),

			$this->mergeWhen((bool) auth()->user(), [
				'id' => $this->id,
				'created_at' => $this->created_at,
			]),
		];
	}
}
