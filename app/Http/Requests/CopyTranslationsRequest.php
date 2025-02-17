<?php

namespace App\Http\Requests;

use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CopyTranslationsRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function prepareForValidation(): void
	{
		$this->merge([
			'from' => (int) $this->get('from'),
			'to' => (int) $this->get('to'),
		]);
	}

	public function rules(): array
	{
		return [
			'from' => [
				'required',
				'numeric',
				Rule::in(
					Country::where('active', true)
						->pluck('id')
						->toArray()
				),
			],
			'to' => [
				'required',
				'numeric',
				Rule::in(
					Country::where('active', true)
						->pluck('id')
						->toArray()
				),
			],
		];
	}
}
