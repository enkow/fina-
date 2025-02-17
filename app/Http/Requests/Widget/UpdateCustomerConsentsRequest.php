<?php

namespace App\Http\Requests\Widget;

use App\Enums\AgreementType;
use App\Models\Club;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerConsentsRequest extends FormRequest
{
	public function authorize(): bool
	{
		return session()->has('customer_id');
	}

	public function rules(): array
	{
		$club = Club::where('slug', request()->get('club_slug'))->first();
		$requiredAgreements = $club
			->agreements()
			->required()
			->whereDoesntHave('customers', function ($query) {
				$query->where('customers.id', session()->get('customer_id'));
			})
			->get();

		return [
			'club_slug' => 'required|exists:clubs,slug',
			'general_terms' => [
				function ($attribute, $value, $fail) use ($requiredAgreements) {
					if (
						!in_array($value, [1, true, 'on', '1', 'true'], true) &&
						count($requiredAgreements->where('type', AgreementType::GeneralTerms))
					) {
						$fail('not accepted');
					}
				},
			],
			'privacy_policy' => [
				function ($attribute, $value, $fail) use ($requiredAgreements) {
					if (
						!in_array($value, [1, true, 'on', '1', 'true'], true) &&
						count($requiredAgreements->where('type', AgreementType::PrivacyPolicy))
					) {
						$fail('not accepted');
					}
				},
			],
			'marketing_agreement' => [
				function ($attribute, $value, $fail) use ($requiredAgreements) {
					if (
						!in_array($value, [1, true, 'on', '1', 'true'], true) &&
						count($requiredAgreements->where('type', AgreementType::MarketingAgreement))
					) {
						$fail('not accepted');
					}
				},
			],
		];
	}
}
