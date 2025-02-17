<?php

namespace App\Http\Requests\Club;

use App\Enums\AnnouncementType;
use App\Rules\AnnouncementDateDoesntExist;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return match (true) {
			!auth()
				->user()
				->isType(['admin', 'manager']) &&
				in_array(
					$this->route('announcement')->type,
					[AnnouncementType::Widget, AnnouncementType::Calendar],
					true
				)
				=> false,
			default => true,
		};
	}

	public function prepareForValidation(): void
	{
		$this->merge([
			'end_at' => $this->get('end_at', $this->get('start_at')),
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
			'type' => ['required', 'numeric', Rule::in(array_column(AnnouncementType::cases(), 'value'))],
			'end_at' => 'date_format:Y-m-d',
			'data' => 'array',
		] +
			match (AnnouncementType::from($this->type)) {
				AnnouncementType::Panel => [
					'start_at' => [
						'date_format:Y-m-d',
						new AnnouncementDateDoesntExist(AnnouncementType::from($this->type)),
					],
					'end_at' => 'same:start_at',
					'content' => 'required|max:500',
				],
				AnnouncementType::Widget => [
					'game_id' => 'required|exists:games,id',
					'start_at' => 'required|date_format:Y-m-d',
					'end_at' => 'required|date_format:Y-m-d',
					'content' => 'required|max:4000',
				],
				AnnouncementType::Calendar => [
					'start_at' => [
						'date_format:Y-m-d',
						new AnnouncementDateDoesntExist(AnnouncementType::from($this->type)),
					],
					'game_id' => 'required|exists:games,id',
					'content_top' => 'required|max:4000',
					'content_bottom' => 'required|max:4000',
				],
			};
	}

	public function attributes(): array
	{
		return [
			'content' => strtolower(__('announcement.content')),
		];
	}
}
