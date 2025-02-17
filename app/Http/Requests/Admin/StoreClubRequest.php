<?php

namespace App\Http\Requests\Admin;

use App\Models\Club;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreClubRequest extends FormRequest
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
		while (Club::where('slug', $slug = strtolower(Str::random(10)))->exists()) {
		}
		$this->merge([
			'slug' => $slug,
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
		];
	}
}
