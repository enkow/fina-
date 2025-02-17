<?php

namespace App\Http\Requests\Admin;

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
			'first_name' => 'required|string|min:2|max:200',
			'last_name' => 'nullable|string|min:2|max:200',
			'phone' => 'required|string|min:5|max:100',
			'email' => 'nullable|email|max:255',
		];
	}
}
