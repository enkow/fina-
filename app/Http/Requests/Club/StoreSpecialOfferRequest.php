<?php

namespace App\Http\Requests\Club;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpecialOfferRequest extends FormRequest
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
			'active_by_default' => !$this->get('active_by_default'),
			'active_week_days' => array_map(static function ($item) {
				return (int) $item;
			}, $this->active_week_days),
			'time_range' => [
				'start' => $this->mergeRanges($this->time_range['start'], 'H:i'),
				'end' => $this->mergeRanges($this->time_range['end'], 'H:i'),
			],
			'when_applies' => $this->mergeRanges($this->when_applies, 'Y-m-d'),
			'when_not_applies' => $this->mergeRanges($this->when_not_applies, 'Y-m-d'),
			'duration' => $this->duration
				? (int) explode(':', $this->duration)[0] * 60 + (int) explode(':', $this->duration)[1]
				: null,
		]);
	}

	private function mergeRanges(array $timeRangeArray, $format = 'H:i'): array
	{
		$filtered_ranges = array_filter($timeRangeArray, function ($range) use ($format) {
			return $range['from'] !== null &&
				$range['to'] !== null &&
				(strtotime($range['from']) < strtotime($range['to']) ||
					($format === 'Y-m-d' && $range['from'] === $range['to']));
		});

		usort($filtered_ranges, function ($a, $b) {
			return strtotime($a['from']) - strtotime($b['from']);
		});

		$result = [];

		foreach ($filtered_ranges as $range) {
			if (empty($result) || strtotime($range['from']) > strtotime(end($result)['to'])) {
				// Add a new range if the result is empty or the range does not overlap with the last added promotion
				$result[] = $range;
			} else {
				// Combine overlapping ranges
				$last_range = array_pop($result);
				$merged_range = [
					'from' => $last_range['from'],
					'to' => max(strtotime($last_range['to']), strtotime($range['to'])),
				];
				$merged_range['to'] = date($format, $merged_range['to']);
				$result[] = $merged_range;
			}
		}

		return $result;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, mixed>
	 */
	public function rules(): array
	{
		return [
			'value' => 'required|integer|min:1|max:100',
			'active_by_default' => 'required|boolean',
			'game_id' => 'exists:games,id',
			'name' => 'required|max:150',
			'description' => 'nullable|string|max:255',
			'active_week_days' => 'array|min:1',
			'duration' => 'nullable|integer|max:1000000',
			'slots' => 'nullable|integer|max:1000000',
			'time_range_type' => 'string|in:start,end',
			'time_range.start' => [
				'array',
				function ($attribute, $value, $fail) {
					if ($this->time_range_type !== explode('.', $attribute)[1]) {
						return;
					}
					$this->checkDateRangeFields($attribute, $value, $fail);
				},
				Rule::requiredIf($this->all()['time_range_type'] === 'start'),
			],
			'time_range.end' => [
				'array',
				function ($attribute, $value, $fail) {
					if ($this->time_range_type !== explode('.', $attribute)[1]) {
						return;
					}
					$this->checkDateRangeFields($attribute, $value, $fail);
				},
				Rule::requiredIf($this->all()['time_range_type'] === 'end'),
			],
			'when_applies' => [
				'array',
				function ($attribute, $value, $fail) {
					$this->checkDateRangeFields($attribute, $value, $fail);
				},
			],
			'when_not_applies' => [
				'array',
				function ($attribute, $value, $fail) {
					$this->checkDateRangeFields($attribute, $value, $fail);
				},
			],
		];
	}

	private function checkDateRangeFields($attribute, $value, $fail)
	{
		foreach ($value as $item) {
			if (empty($item['from']) || empty($item['to'])) {
				$fail(__('special-offer.complete-all-fields'));
			}
		}
	}

	public function attributes(): array
	{
		return [
			'game_id' => ucfirst(__('main.type')),
		];
	}
}
