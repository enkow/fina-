<?php

namespace App\Http\Requests\Club;

use App\Models\Game;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManagerEmailRequest extends FormRequest
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

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function rules(): array
	{
		return [
			'game_id' => [
				'exists:games,id',
				function ($attribute, $value, $fail) {
					if (!club()->games->contains(Game::find($value))) {
						$fail('Your club does not have access to this game');
					}
				},
			],
			'email' => [
				'required',
				'email',
				'max:200',
				Rule::unique('manager_emails')->where(
					fn(Builder $query) => $query->where('game_id', $this->request->get('game_id'))
				),
			],
		];
	}
}
