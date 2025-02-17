<?php

namespace App\Http\Requests\Club;

use App\Models\Slot;
use Illuminate\Foundation\Http\FormRequest;

class SearchSlotRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function rules(): array
	{
		return [
			'active' => 'nullable|boolean',
			'slot_id' => [
				'nullable',
				function ($attribute, $value, $fail) {
					if (!in_array($value, [0, '0', null], true) && !Slot::where('id', $value)->exists()) {
						$fail('Invalid slot id attribute');
					}
				},
			],
		];
	}
}
