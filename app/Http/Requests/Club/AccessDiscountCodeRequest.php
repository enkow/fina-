<?php

namespace App\Http\Requests\Club;

use Illuminate\Foundation\Http\FormRequest;

class AccessDiscountCodeRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		if ($this->route('discount_code')->club_id !== clubId()) {
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
				//
			];
	}
}
