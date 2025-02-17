<?php

namespace App\Http\Requests\Club;

use Illuminate\Foundation\Http\FormRequest;

class CreateOpeningHoursExceptionRequest extends FormRequest
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
	public function rules()
	{
		return [
				//
			];
	}
}
