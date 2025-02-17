<?php

namespace App\Http\Requests\Widget;

use App\Enums\CustomerVerificationMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerPhoneRequest extends FormRequest
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
			'phone.required' => 'validation.required',
			'phone.max' => 'validation.max.numeric',
			'phone.min' => 'validation.min.numeric',
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
			'phone' => [
                'required',
                'min:7',
                'max:100',
                function ($attribute, $value, $fail) {
                    if ($this->route('club')->customer_verification_type === CustomerVerificationMethod::SMS) {
                        $query = $this->route('club')?->customers()
                            ->whereRaw("REPLACE(REPLACE(phone, ' ', ''), '-', '') = ?", [str_replace([' ', '-'], '', $value)])
                            ->whereNull('deleted_at');
                        if ($query->exists()) {
                            $fail(__('validation.unique', ['attribute' => __('validation.attributes.phone_number')]));
                        }
                    }
                },
            ],
		];
	}
}
