<?php

namespace App\Http\Requests\Widget;

use Illuminate\Foundation\Http\FormRequest;

class CustomerPasswordRecoveryRequest extends FormRequest
{
	public function authorize(): bool
	{
		return $this->route('customer')->isPasswordRecoveryTokenValid($this->route('token'));
	}

	public function rules(): array
	{
		return [
			'password' => 'required|confirmed',
			'widget_channel' => 'required',
		];
	}
}
