<?php

namespace App\Http\Resources;

use App\Enums\AgreementContentType;
use App\Models\Club;
use Illuminate\Http\Resources\Json\JsonResource;

class AgreementResource extends JsonResource
{
	public function toArray($request): array
	{
		return [
			'id' => $this->id,
			'active' => $this->active,
			'required' => $this->required,
			'club_id' => $this->club_id,
			'club' => new ClubResource(Club::getClub($this->club_id)),
			'content_type' => $this->content_type,
			'is_filled' =>
				$this->content_type === AgreementContentType::Text
					? $this->text !== null && $this->text !== ''
					: $this->file !== null,
			'text' => $this->text,
			'file' => $this->file,
			'type' => $this->type,
			'created_at' => $this->created_at,
		];
	}
}
