<?php

namespace App\Http\Requests\Club;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePricelistRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return match (true) {
			$this->route('pricelist')->club_id !== clubId() => false,
			$this->route('pricelist')->game_id !== $this->route('game')->id => false,
			!club()
				->getGames()
				->contains($this->route('game'))
				=> false,
			!auth()
				->user()
				->isType(['admin', 'manager'])
				=> false,
			default => true,
		};
	}

	public function prepareForValidation(): void
	{
		$daysArray = array_map(static function ($dayItems) {
			return array_map(static function ($dayItem) {
				$price = str_replace(',', '.', $dayItem['price']);
				if (is_numeric($price)) {
					$price = (int) round((float) $price * 100);
				}

				return [
					'from' => $dayItem['from'] === '24:00' ? '23:59' : $dayItem['from'],
					'to' => $dayItem['to'] === '24:00' ? '23:59' : $dayItem['to'],
					'price' => $price,
				];
			}, $dayItems);
		}, $this->get('days'));

		$this->merge([
			'days' => $daysArray,
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
			'name' => [
				'required',
				'string',
				'min:1',
				'max:100',
				Rule::unique('pricelists', 'name')
					->where(
						fn(Builder $query) => $query
							->where('game_id', $this->route('game')->id)
							->where('club_id', clubId())
					)
					->whereNull('deleted_at')
					->ignore($this->route('pricelist')->id),
			],
			'days' => 'required|array|min:7|max:7',
			'days.*' => [
				'required',
				'array',
				'min:1',
				'max:20',
				function ($attribute, $value, $fail) {
					$timeRanges = $this->get('days')[explode('.', $attribute)[1]];
					if ($this->areTimeRangesOverlapping($timeRanges)) {
						$fail(__('pricelist.time-ranges-overlap'));
					}
				},
				function ($attribute, $value, $fail) {
					if ($value[count($value) - 1]['to'] !== '23:59' || $value[0]['from'] !== '00:00') {
						$fail(__('pricelist.pricelist-all-day'));
					}
				},
			],
			'days.*.*.from' => 'required|date_format:H:i',
			'days.*.*.to' => 'required|date_format:H:i',
			'days.*.*.price' => 'required|integer|min:0|max:1000000',
		];
	}

	public function areTimeRangesOverlapping($timeRanges): bool
	{
		usort($timeRanges, function ($a, $b) {
			return strtotime($a['from']) <=> strtotime($b['from']);
		});

		for ($i = 1; $i < count($timeRanges); $i++) {
			$prevTo = strtotime($timeRanges[$i - 1]['to']);
			$currentFrom = strtotime($timeRanges[$i]['from']);

			if ($prevTo > $currentFrom) {
				return true;
			}
		}

		return false;
	}
}
