<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreHelpItemRequest extends FormRequest
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
		return [
			'video_url' => 'nullable|url|min:1|max:255',
			'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
			'title' => 'required|string|min:1|max:200',
			'description' => 'max:500',
			'content' => 'max:10000',
			'weight' => 'integer|min:-100|max:1000000',
		];
	}
}
