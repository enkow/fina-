<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class HelpItemResource extends JsonResource
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
			'thumbnail' => $this->thumbnail,
			'video_url' => $this->video_url,
			'title' => $this->title,
			'description' => $this->description,
			'content' => $this->content,
			'weight' => $this->weight,

			'help_section_id' => $this->help_section_id,
			'helpSection' => new HelpSectionResource($this->whenLoaded('helpSection')),
			'helpItemImages' => HelpItemImageResource::collection($this->whenLoaded('helpItemImages')),

			$this->mergeWhen(
				auth()
					->user()
					?->isType('admin'),
				[
					'created_at' => $this->created_at,
				]
			),
		];
	}
}
