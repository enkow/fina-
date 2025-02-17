<?php

namespace App\Http\Requests\Club;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		if ($this->route('customer')->club_id !== clubId()) {
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
			'email' => 'nullable|email|max:255',
			'first_name' => 'required|string|max:100',
			'last_name' => 'nullable|string|max:100',
			'phone' => 'required|string|max:30',
		];
	}
}
