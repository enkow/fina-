<?php

namespace App\Http\Requests;

use App\Models\TablePreference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTablePreferenceRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		if (
			!in_array($this->route('table_name'), ['reservations', 'slots']) &&
			!isset(config('table-preferences')[$this->route('table_name')])
		) {
			return false;
		}

		return true;
	}

	public function prepareForValidation()
	{
		$dataArray = [];
		$tablePreference = TablePreference::getColumns(
			tableName: $this->route('table_name'),
			userId: auth()->user()->id,
			tableNamePostfix: request()?->get('tableNamePostfix', null)
		);
		foreach ($this->get('data') as $item) {
			if (in_array($item['key'], $tablePreference, true)) {
				$dataArray[] = $item;
			}
		}
		$this->merge([
			'data' => $dataArray,
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
			'data' => 'required|array',
			'data.*.key' => [
				'required',
				'string',
				Rule::in(
					TablePreference::getColumns(
						tableName: $this->route('table_name'),
						userId: auth()->user()->id,
						tableNamePostfix: request()?->get('tableNamePostfix', null)
					)
				),
			],
			'data.*.enabled' => ['required', 'boolean'],
		];
	}
}
