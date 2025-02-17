<?php

namespace App\Http\Requests\Club;

use App\Custom\Timezone;
use Illuminate\Foundation\Http\FormRequest;

class StorePricelistExceptionRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		if (
			$this->route('pricelist')->club_id !== clubId() ||
			$this->route('pricelist')->game_id !== $this->route('game')->id
		) {
			return false;
		}

		return true;
	}

	public function prepareForValidation(): void
	{
		$array = array_map(static function ($item) {
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
		}, $this->all()['entries']);

		$this->replace(['overlapping' => '-', 'entries' => $array]);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function rules(): array
	{
		return [
			'overlapping' => [
				function ($attribute, $value, $fail) {
					$timeRanges = $this->all()['entries'] ?? [];
					if ($this->areTimeRangesOverlapping($timeRanges)) {
						$fail(__('pricelist-exception.datetime-ranges-overlap'));
					}
				},
			],
			'entries.*.start_at' => 'required|date_format:Y-m-d',
			'entries.*.end_at' => 'required|date_format:Y-m-d',
			'entries.*.from' => 'required|date_format:H:i',
			'entries.*.to' => 'required|date_format:H:i',
			'entries.*.price' => 'required|integer|min:0|max:1000000',
		];
	}

	function areTimeRangesOverlapping($timeRanges): bool
	{
		usort($timeRanges, function ($a, $b) {
			$startAtComparison = strcmp($a['start_at'], $b['start_at']);
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

			if ($prev['end_at'] >= $current['start_at'] && $prevTo > $currentFrom) {
				return true;
			}
		}

		return false;
	}
}
