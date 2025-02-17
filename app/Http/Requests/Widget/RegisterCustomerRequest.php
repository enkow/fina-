<?php

namespace App\Http\Requests\Widget;

use App\Enums\AgreementType;
use App\Enums\CustomerVerificationMethod;
use App\Models\Club;
use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterCustomerRequest extends FormRequest
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
			'first_name' => 'first_name',
			'last_name' => 'last_name',
			'email' => 'email',
			'password' => 'password',
			'phone' => 'phone',
		];
	}

	public function messages(): array
	{
		return [
			'first_name.required' => 'validation.required',
			'first_name.max' => 'validation.max.numeric',
			'last_name.required' => 'validation.required',
			'last_name.max' => 'validation.max.numeric',
			'email.required' => 'validation.required',
			'email.max' => 'validation.max.numeric',
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
		$club = Club::where('slug', request()->get('club_slug'))->first();
		$requiredAgreements = $club
			->agreements()
			->required()
			->get();

		return [
			'club_slug' => 'required|exists:clubs,slug',
			'first_name' => 'required|max:100',
			'last_name' => 'required|max:100',
			'email' => [
				'required',
				'email',
				'max:255',
                function ($attribute, $value, $fail) use ($club) {
                    if ($club->customer_verification_type === CustomerVerificationMethod::MAIL) {
                        $query = $club->customers()
                            ->where('email', $value)
                            ->whereNull('deleted_at');
                        if ($query->exists()) {
                            $fail(__('validation.unique', ['attribute' => __('validation.attributes.email')]));
                        }
                    }
                },
			],
			'password' => [
                'required',
                'max:100'
            ],
			'phone' => [
                'required',
                'min:7',
                'max:100',
                function ($attribute, $value, $fail) use ($club) {
                    if ($club->customer_verification_type === CustomerVerificationMethod::SMS) {
                        $query = $club->customers()
                            ->whereRaw("REPLACE(REPLACE(phone, ' ', ''), '-', '') = ?", [str_replace([' ', '-'], '', $value)])
                            ->whereNull('deleted_at');
                        if ($query->exists()) {
                            $fail(__('validation.unique', ['attribute' => __('validation.attributes.phone_number')]));
                        }
                    }
                },
            ],
			'consents.general_terms' => [
				function ($attribute, $value, $fail) use ($requiredAgreements) {
					if (
						!in_array($value, [1, true, 'on', '1', 'true'], true) &&
						count($requiredAgreements->where('type', AgreementType::GeneralTerms))
					) {
						$fail('widget.consent-is-required');
					}
				},
			],
			'consents.privacy_policy' => [
				function ($attribute, $value, $fail) use ($requiredAgreements) {
					if (
						!in_array($value, [1, true, 'on', '1', 'true'], true) &&
						count($requiredAgreements->where('type', AgreementType::PrivacyPolicy))
					) {
						$fail('widget.consent-is-required');
					}
				},
			],
			'consents.marketing_agreement' => [
				function ($attribute, $value, $fail) use ($requiredAgreements) {
					if (
						!in_array($value, [1, true, 'on', '1', 'true'], true) &&
						count($requiredAgreements->where('type', AgreementType::MarketingAgreement))
					) {
						$fail('widget.consent-is-required');
					}
				},
			],
			'widget_channel' => ['required', 'string', 'min:1', 'max:1000'],
		];
	}
}
