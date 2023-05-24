<?php

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Form\Field;

/**
 * Provides an input field for files
 */
class File extends Field
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'File';

	/**
	 * Method to get the field input markup for the file field.
	 * Field attributes allow specification of a maximum file size and a string
	 * of accepted file extensions.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$attributes = array(
			'type'         => 'file',
			'name'         => $this->name,
			'id'           => $this->id,
			'size'         => ($this->element['size'] ? (int) $this->element['size'] : ''),
			'accept'       => ($this->element['accept'] ? (string) $this->element['accept'] : ''),
			'class'        => ($this->element['class'] ? (string) $this->element['class'] : ''),
			'disabled'     => ((string) $this->element['disabled'] == 'true' ? 'disabled' : ''),
			'required'     => ((string) $this->element['required'] == 'true' ? 'required' : ''),
			'onchange'     => ($this->element['onchange'] ? (string) $this->element['onchange'] : '')
		);

		$attr = array();
		foreach ($attributes as $key => $value)
		{
			$attr[] = $key . '="' . e($value) . '"';
		}
		$attr = implode(' ', $attr);

		return '<input ' . $attr . ' value="" />';
	}
}
