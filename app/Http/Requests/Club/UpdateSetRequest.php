<?php

namespace App\Http\Requests\Club;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSetRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return match (true) {
			$this->route('set')->club_id !== clubId() => false,
			!auth()
				->user()
				->isType(['admin', 'manager'])
				=> false,
			default => true,
		};
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
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
			'photo' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp|max:5120',
			'mobile_photo' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp|max:5120',
			'description' => 'nullable|max:1000',
			'price' => 'required|integer|min:0|max:1000000',
			'quantity.*' => 'nullable|integer|min:0|max:1000000000',
		];
	}
}
