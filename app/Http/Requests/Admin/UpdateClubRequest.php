<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Country;

class UpdateClubRequest extends FormRequest
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

	public function prepareForValidation(): void
	{
		$widgetCountries = $this->get('widget_countries');
		$availableCountries = Country::where('active', 1)
			->pluck('code')
			->all();
		$correctWidgetCountries = [];
		foreach (explode(',', $widgetCountries) as $widgetCountry) {
			if (in_array(strtoupper($widgetCountry), $availableCountries, true)) {
				$correctWidgetCountries[] = $widgetCountry;
			}
		}
		$this->merge([
			'invoice_emails' => array_map('trim', explode("\n", $this->input('invoice_emails'))),
			'widget_countries' => $correctWidgetCountries,
		]);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function rules(): array
	{
		return [
			'country_id' => 'required|exists:countries,id',
			'name' => 'required|string|max:100',
			'description' => 'nullable|string',
			'slug' => 'required|min:3|max:100|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
			'address' => 'string|max:100',
			'postal_code' => 'string|max:20',
			'city' => 'string|max:100',
			'phone_number' => 'string|max:30',
			'email' => 'email|max:100',
			'vat_number' => 'string|max:100',
			'first_login_message' => 'nullable|string|max:20000',
			'invoice_emails' => 'nullable|array',
			'invoice_emails.*' => 'email',
		];
	}
}
