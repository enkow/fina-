<?php

namespace App\Http\Requests\Club;

use Illuminate\Foundation\Http\FormRequest;

class AccessAgreementRequest extends FormRequest
{
	public function authorize(): bool
	{
		return $this->route('agreement')->club_id === clubId();
	}

	public function rules(): array
	{
		return [];
	}
}
