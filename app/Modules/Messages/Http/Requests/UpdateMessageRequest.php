<?php

namespace App\Modules\Messages\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMessageRequest extends BaseFormRequest
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'userid' => 'nullable|integer|min:1',
			'messagequeuetypeid' => 'nullable|integer|min:1',
			'targetobjectid' => 'nullable|integer|min:1',
			'messagequeueoptionsid' => 'nullable|integer|min:1',
			'pid' => 'nullable|integer|min:1',
			'datetimestarted' => 'nullable|date',
			'datetimecompleted' => 'nullable|date',
			'returnstatus' => 'nullable|integer|min:1',
		];
	}

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	/*public function authorize()
	{
		return true;
	}*/

	/**
	 * Get custom messages for validator errors.
	 *
	 * @return array
	 */
	/*public function messages()
	{
		return [
			'messagequeuetypeid.min' => 'messagequeuetypeid must be greater than ',
			'targetobjectid.required' => 'targetobjectid is required!',
		];
	}*/
}
