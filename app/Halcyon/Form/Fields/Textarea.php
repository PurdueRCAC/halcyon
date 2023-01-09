<?php

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Form\Field;

/**
 * Supports a multi line area for entry of plain text
 */
class Textarea extends Field
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Textarea';

	/**
	 * Method to get the textarea field input markup.
	 * Use the rows and columns attributes to specify the dimensions of the area.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$attributes = array(
			'type'         => 'text',
			'name'         => $this->name,
			'id'           => $this->id,
			'class'        => ($this->element['class']     ? (string) 'form-control ' . $this->element['class']  : 'form-control'),
			'cols'         => ($this->element['cols'] ? (int) $this->element['cols'] : ''),
			'rows'         => ($this->element['rows'] ? (int) $this->element['rows'] : ''),
			'disabled'     => ((string) $this->element['disabled'] == 'true'    ? 'disabled' : ''),
			'readonly'     => ((string) $this->element['readonly'] == 'true'    ? 'readonly' : ''),
			'required'     => ((string) $this->element['required'] == 'true'    ? 'required' : ''),
			'onchange'     => ($this->element['onchange']  ? (string) $this->element['onchange'] : '')
		);

		$attr = array();
		foreach ($attributes as $key => $value)
		{
			if (!$value)
			{
				continue;
			}

			$attr[] = $key . '="' . $value . '"';
		}
		$attr = implode(' ', $attr);

		return '<textarea ' . $attr . '>' . htmlspecialchars((string)$this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';
	}
}
