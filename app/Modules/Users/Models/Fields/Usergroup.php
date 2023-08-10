<?php
namespace App\Modules\Users\Models\Fields;

use App\Halcyon\Form\Field;
use App\Halcyon\Html\Builder\Select as Dropdown;
use App\Halcyon\Html\Builder\Access;

/**
 * Supports a nested check box field listing user groups.
 * Multiselect is available by default.
 */
class Usergroup extends Field
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Usergroup';

	/**
	 * Method to get the user group field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Initialize variables.
		$options = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="form-control ' . (string) $this->element['class'] . '"' : ' class="form-control"';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		return Access::usergroup($this->name, $this->value, $attr, false, $this->id);
	}
}
