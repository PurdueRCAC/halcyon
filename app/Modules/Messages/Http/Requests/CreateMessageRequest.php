<?php

namespace App\Modules\Messages\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMessageRequest extends BaseFormRequest
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string,string>
	 */
	public function rules()
	{
		return [
			'messagequeuetypeid' => 'required|integer|min:1',
			'targetobjectid' => 'required|integer|min:1',
		];
	}

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get custom messages for validator errors.
	 *
	 * @return array<string,string>
	 */
	public function messages()
	{
		return [
			'messagequeuetypeid.required' => 'messagequeuetypeid is required!',
			'targetobjectid.required' => 'targetobjectid is required!',
		];
	}

	/**
	 *  Filters to be applied to the input.
	 *
	 * @return array<string,string>
	 */
	/*public function filters()
	{
		return [
			'email' => 'trim|lowercase',
			'name' => 'trim|capitalize|escape'
		];
	}*/
}
