<?php

namespace App\Http\Requests\Club;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationTypeRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return auth()
			->user()
			->isType(['admin', 'manager']);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function rules(): array
	{
		return [
			'name' => [
				'required',
				'string',
				'min:1',
				'max:50',
				Rule::unique('reservation_types', 'name')
					->where('club_id', clubId())
					->whereNull('deleted_at'),
			],
			'color' => [
				'required',
				'string',
				Rule::unique('reservation_types', 'color')
					->where('club_id', clubId())
					->whereNull('deleted_at'),
				'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/',
			],
		];
	}
}
