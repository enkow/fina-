<?php

namespace App\Http\Resources;

use App\Custom\Timezone;
use App\Models\Game;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		return [
			'id' => $this->id,

			$this->mergeWhen($this->whenLoaded('model'), [
				'model' =>
					$this->model?->getMorphClass() === (new Game())->getMorphClass()
						? new GameResource($this->model)
						: new ProductResource($this->model),
			]),

			'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
			'settings' => $this->settings,
			'details' => $this->details,
			'total' => $this->total,
			'created_at' => Timezone::convertToLocal($this->created_at)->format('Y-m-d H:i:s'),
		];
	}
}
