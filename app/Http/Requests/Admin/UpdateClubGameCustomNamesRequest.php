<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClubGameCustomNamesRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		if (!$this->route('club')->games->contains($this->route('game'))) {
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
			'*.fee_fixed' => 'required|min:0|regex:/^\d+(\.\d+)?$/',
			'*.fee_percent' => 'required|min:0|regex:/^\d+(\.\d+)?$/',
		];
	}
}
