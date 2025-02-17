<?php

namespace App\Http\Requests\Club;

use App\Enums\AnnouncementType;
use Illuminate\Foundation\Http\FormRequest;

class AccessAnnouncementRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return match (true) {
			!auth()
				->user()
				->isType(['admin', 'manager']) &&
				in_array(
					$this->route('announcement')->type,
					[AnnouncementType::Widget, AnnouncementType::Calendar],
					true
				)
				=> false,
			$this->route('announcement')->club_id !== clubId() => false,
			default => true,
		};
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function rules(): array
	{
		return [
				//
			];
	}
}
