<?php

namespace App\Http\Requests\Club;

use Illuminate\Foundation\Http\FormRequest;

class DestroySetRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return match (true) {
			$this->route('set')->club_id !== clubId() => false,
			!auth()
				->user()
				->isType(['admin', 'manager'])
				=> false,
			default => true,
		};
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
