<?php

namespace App\Http\Resources;

use App\Custom\Timezone;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		$vatRateNumber = is_numeric($this->vat) ? $this->vat : 0;
		return [
			'id' => $this->id,
			'title' => $this->title,
			'total_net' => $this->total,
			'total_gross' => $this->total + round(($this->total * $vatRateNumber) / 100),
			'from' => $this->from,
			'to' => $this->to,
			'fakturownia_token' => $this->fakturownia_token,
			'created_at' => Timezone::convertToLocal($this->created_at)->format('Y-m-d H:i:s'),

			'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
		];
	}
}
