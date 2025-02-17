<?php

namespace App\Http\Requests\Widget;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginCustomerRequest extends FormRequest
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

	public function attributes(): array
	{
		return [
			'email' => 'email',
			'password' => 'password',
		];
	}

	public function messages(): array
	{
		return [
			'email.required' => 'validation.required',
			'email.email' => 'validation.email',
			'email.max' => 'validation.max.numeric',
			'email.exists' => 'validation.exists',
			'password.required' => 'validation.required',
			'password.max' => 'validation.max.numeric',
		];
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function rules(): array
	{
		return [
			'club_slug' => 'required|exists:clubs,slug',
			'email' => [
				'required',
				'email',
				'max:100',
				Rule::exists('customers', 'email')
					->when($this->route('club'), function ($query) {
						$query->where('club_id', $this->route('club')->id);
					})
					->whereNull('deleted_at'),
			],
			'password' => 'required|max:100',
		];
	}
}
