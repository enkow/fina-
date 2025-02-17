<?php

namespace App\Http\Requests\Club;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		if ($this->route('employee')->club_id !== clubId()) {
			return false;
		}

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
			'type' => ['required', 'in:manager,employee'],
			'first_name' => 'required|min:3|max:100',
			'last_name' => 'required|min:3|max:100',
			'email' => [
				'required',
				Rule::unique('users', 'email')->whereNot('id', $this->route('employee')->id),
				'email',
				'min:3',
				'max:100',
			],
		];
	}
}
