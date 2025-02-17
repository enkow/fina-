<?php

namespace App\Http\Requests\Club;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBillingDetailsRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return $this->user()->club_id !== null;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function rules()
	{
		return [
			'billing_name' => 'required|string|max:255',
			'billing_address' => 'required|string|max:255',
			'billing_postal_code' => 'required|string|max:255',
			'billing_city' => 'required|string|max:255',
			'billing_nip' => 'required|string|max:255',
			'country_id' => 'required|exists:countries,id',
			'invoice_lang' => 'required|string|max:3',
		];
	}
}
