<?php

namespace App\Http\Requests\Club;

use App\Enums\DiscountCodeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDiscountCodeRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		if ($this->route('discount_code')->club_id !== clubId()) {
			return false;
		}

		return true;
	}

	public function prepareForValidation(): void
	{
		$this->merge([
			'value' =>
				$this->type === DiscountCodeType::Amount->value
					? amountToSmallestUnits($this->value)
					: $this->value,
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
			'code' => 'required|string|max:50',
			'game_id' => 'required|exists:games,id',
			'type' => ['required', 'integer', Rule::in(array_column(DiscountCodeType::cases(), 'value'))],
			'value' => [
				'required',
				'numeric',
				'min:0',
				function ($attribute, $value, $fail) {
					if (
						request()->get('type') === DiscountCodeType::Amount->value &&
						(int) $value > 1000000
					) {
						$fail(
							__('validation.max.numeric', ['attribute' => __('main.discount'), 'max' => 10000])
						);
					}
					if (request()->get('type') === DiscountCodeType::Percent->value && (int) $value > 100) {
						$fail(
							__('validation.max.numeric', ['attribute' => __('main.discount'), 'max' => 100])
						);
					}
				},
			],
			'code_quantity' => 'nullable|numeric|min:0|max:1000000',
			'code_quantity_per_customer' => 'nullable|numeric|min:0|max:1000000',
			'start_at' => 'nullable|date_format:Y-m-d H:i',
			'end_at' => 'nullable|date_format:Y-m-d H:i',
		];
	}

	public function attributes(): array
	{
		return [
			'game_id' => __('main.type'),
			'code' => strtolower(__('discount-code.code')),
			'value' => strtolower(__('main.discount')),
		];
	}
}
