<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGameRequest extends FormRequest
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
			'game_id' => 'nullable|numeric|exists:games,id',
			'name' => 'required|string|min:3|max:100',
			'description' => 'nullable|string|max:255',
			'icon' => 'nullable|string|max:50000',
			'photo' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp|max:5120',
			'setting_icon_color' => ['required', 'string', 'in:info,brand,warning,danger,brand,gray'],
		];
	}
}
