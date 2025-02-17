<?php

namespace App\Http\Requests\Club;

use App\Custom\Timezone;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePricelistRequest extends FormRequest
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
				->isType(['admin', 'manager'])
				=> false,
			!club()->games->contains($this->route('game')) => false,
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

		$exceptions = array_map(static function ($item) {
			$price = str_replace(',', '.', $item['price']);
			if (is_numeric($price)) {
				$price = (int) round((float) $price * 100);
			}

			return [
				'from' => $item['from'] === '24:00' ? '23:59' : $item['from'],
				'to' => $item['to'] === '24:00' ? '23:59' : $item['to'],
				'start_at' => Timezone::convertToLocal($item['start_at'])->format('Y-m-d'),
				'end_at' => Timezone::convertToLocal($item['end_at'])->format('Y-m-d'),
				'price' => $price,
			];
		}, $this->all()['exceptions']);

		$this->merge([
			'days' => $daysArray,
			'exceptions' => $exceptions,
			'exceptions-overlapping' => '-',
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
					->whereNull('deleted_at'),
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
			'exceptions-overlapping' => [
				function ($attribute, $value, $fail) {
					$timeRanges = $this->all()['exceptions'] ?? [];
					if ($this->areTimeRangesOverlapping($timeRanges)) {
						$fail(__('pricelist-exception.datetime-ranges-overlap'));
					}
				},
			],
			'exceptions.*.start_at' => 'required|date_format:Y-m-d',
			'exceptions.*.end_at' => 'required|date_format:Y-m-d',
			'exceptions.*.from' => 'required|date_format:H:i',
			'exceptions.*.to' => 'required|date_format:H:i',
			'exceptions.*.price' => 'required|integer|min:0|max:1000000',
		];
	}

	public function areTimeRangesOverlapping($timeRanges): bool
	{
		usort($timeRanges, function ($a, $b) {
			$startAtComparison = strcmp($a['from'], $b['from']);
			if ($startAtComparison !== 0) {
				return $startAtComparison;
			}

			return strtotime($a['from']) <=> strtotime($b['from']);
		});

		for ($i = 1; $i < count($timeRanges); $i++) {
			$prev = $timeRanges[$i - 1];
			$current = $timeRanges[$i];

			$prevTo = strtotime($prev['to']);
			$currentFrom = strtotime($current['from']);

			if ($prev['to'] >= $current['from'] && $prevTo > $currentFrom) {
				return true;
			}
		}

		return false;
	}
}
