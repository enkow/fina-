<?php

namespace App\Http\Requests\Club;

use Illuminate\Foundation\Http\FormRequest;

class AccessHelpItemRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		if (
			$this->route('help_section')->active === false ||
			$this->route('help_item')->active === false ||
			$this->route('help_item')->help_section_id !== $this->route('help_section')->id ||
			club()->country_id !== $this->route('help_section')->country_id
		) {
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
