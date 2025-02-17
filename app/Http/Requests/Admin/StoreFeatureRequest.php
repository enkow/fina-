<?php

namespace App\Http\Requests\Admin;

use App\Models\Feature;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeatureRequest extends FormRequest
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
		$featureTypes = (new Feature())->getChildTypes();

		return [
			'featureType' => [
				'required',
				Rule::in(array_keys($featureTypes)),
				function ($attribute, $value, $fail) use ($featureTypes) {
					$featureType = $featureTypes[$value];
					$conflictedFeatureTypesKeys = array_map(
						static fn($item): string => array_search($item, $featureTypes, true),
						(new $featureType())->conflictedFeatures
					);
					if (
						count(
							$conflictedFeatures = Feature::where('game_id', $this->route('game')->id)
								->whereIn('type', $conflictedFeatureTypesKeys)
								->get()
						)
					) {
						$fail(
							"Nie możesz dodać cechy $value, kiedy dodana jest cecha " .
								$conflictedFeatures->first()->type
						);
					}
				},
				function ($attribute, $value, $fail) use ($featureTypes) {
					$featureType = $featureTypes[$value];
					$requiredFeatureTypeKeys = array_map(
						static fn($item): string => array_search($item, $featureTypes, true),
						(new $featureType())->requiredFeatures
					);
					foreach ($requiredFeatureTypeKeys as $requiredFeatureTypeKey) {
						if (
							!Feature::where('game_id', $this->route('game')->id)
								->where('type', $requiredFeatureTypeKey)
								->exists()
						) {
							$fail(
								"Nie możesz dodać cechy $value, jeśli nie ma dodanej cechy " .
									$requiredFeatureTypeKey
							);
						}
					}
				},
				function ($attribute, $value, $fail) use ($featureTypes) {
					$featureType = $featureTypes[$value];
					if (
						!(new $featureType())->isTaggableIfGameReservationExist &&
						$this->route('game')
							->reservations()
							->count()
					) {
						$fail("Nie możesz dodać cechy $value, kiedy do gry przypisane są rezerwacje");
					}
				},
			],
			'code' => [
				'required',
				Rule::unique('features')->where(function (Builder $query) {
					return $query
						->where('code', $this->get('code'))
						->where('game_id', $this->route('game')->id);
				}),
			],
		];
	}
}
