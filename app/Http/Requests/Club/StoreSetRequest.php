<?php

namespace App\Http\Requests\Club;

use Illuminate\Foundation\Http\FormRequest;

class StoreSetRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return auth()
			->user()
			->isType(['admin', 'manager']);
	}

	public function messages()
	{
		return [
			'price.min' => __('validation.gt.zero', ['attribute' => __('validation.attributes.price')]),
		];
	}

	public function prepareForValidation(): void
	{
		$this->merge([
			'price' => $this->price ? amountToSmallestUnits($this->price) : null,
			'description' => $this->get('description') ?? '',
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
			'name' => 'required|min:2|max:100',
			'photo' => 'required|image|mimes:jpeg,jpg,png,gif,svg,webp|max:5120',
			'price' => 'required|integer|min:0|max:1000000',
			'mobile_photo' => 'required|image|mimes:jpeg,jpg,png,gif,svg,webp|max:5120',
			'description' => 'nullable|max:1000',
			'quantity.*' => 'nullable|integer|min:0|max:1000000000',
		];
	}
}
