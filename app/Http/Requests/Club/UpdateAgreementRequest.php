<?php

namespace App\Http\Requests\Club;

use App\Enums\AgreementContentType;
use App\Enums\AgreementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAgreementRequest extends FormRequest
{
	public function authorize(): bool
	{
		return $this->route('agreement')->club_id === clubId();
	}

	public function rules(): array
	{
		return [
			'type' => ['required', Rule::in(array_column(AgreementType::cases(), 'value'))],
			'content_type' => ['required', Rule::in(array_column(AgreementContentType::cases(), 'value'))],
			'text' => 'nullable|max:1000',
			'file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
		];
	}
}
