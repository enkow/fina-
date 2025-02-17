<?php

namespace App\Http\Requests\Widget;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerForgotPasswordRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function attributes(): array
	{
		return [
			'email' => 'email',
		];
	}

	public function messages(): array
	{
		return [
			'email.required' => 'validation.required',
			'email.email' => 'validation.email',
		];
	}

	public function rules(): array
	{
		return [
			'email' => [
				'required',
				'email',
				Rule::exists('customers', 'email')
					->when($this->route('club'), function ($query) {
						$query->where('club_id', $this->route('club')->id);
					})
					->whereNull('deleted_at'),
			],
		];
	}
}
