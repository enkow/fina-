<?php

namespace App\Http\Requests\Club;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
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
			'type' => ['required', 'in:manager,employee'],
			'first_name' => 'required|min:3|max:100',
			'last_name' => 'required|min:3|max:100',
			'email' => 'required|email|unique:users,email|min:3|max:100',
		];
	}
}
