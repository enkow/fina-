<?php

namespace App\Http\Requests\Admin;

use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCountryRequest extends FormRequest
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
		$activeCountries = Country::where('active', true)->get();

		return [
			'active' => [
				'required',
				'boolean',
				function ($attribute, $value, $fail) use ($activeCountries) {
					if (
						$value === false &&
						count($activeCountries) === 1 &&
						$activeCountries[0]->id === $this->route('country')->id
					) {
						$fail('Nie możesz wyłączyć wszystkich dostępnych krajów');
					}
				},
			],
			'payment_method_type' => 'string|exists:payment_methods,type',
			'currency' => 'required|string|min:1|max:10',
			'locale' => 'required|string|min:1|max:10',
			'dialing_code' => 'required|regex:/^[0-9-]+$/',
		];
	}
}
