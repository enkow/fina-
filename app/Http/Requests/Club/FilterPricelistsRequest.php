<?php

namespace App\Http\Requests\Club;

use App\Models\Game;
use Illuminate\Foundation\Http\FormRequest;

class FilterPricelistsRequest extends FormRequest
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
		$this->merge([
			'game_id' => $this->get('game_id', Game::getCached()->first()->id),
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
			'game_id' => 'exists:games,id',
		];
	}
}
