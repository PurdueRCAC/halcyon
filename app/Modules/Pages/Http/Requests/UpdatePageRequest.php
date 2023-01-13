<?php

namespace App\Modules\Pages\Http\Requests;

use App\Modules\Core\Internationalisation\BaseFormRequest;

class UpdatePageRequest extends BaseFormRequest
{
	/**
	 * @var string
	 */
	protected $translationsAttributesKey = 'page::pages.validation.attributes';

	/**
	 * @return array<string,string>
	 */
	public function rules()
	{
		return [
			'template' => 'required',
		];
	}

	/**
	 * @return array<string,string>
	 */
	public function translationRules()
	{
		return [
			'title' => 'required',
			'slug' => 'required',
		];
	}

	/**
	 * @return true
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * @return array<string,string>
	 */
	public function messages()
	{
		return [
			'template.required' => trans('page::messages.template is required'),
			'is_home.unique' => trans('page::messages.only one homepage allowed'),
		];
	}

	/**
	 * @return array<string,string>
	 */
	public function translationMessages()
	{
		return [
			'title.required' => trans('page::messages.title is required'),
			'slug.required' => trans('page::messages.slug is required'),
			'body.required' => trans('page::messages.body is required'),
		];
	}
}
