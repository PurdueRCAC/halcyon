<?php
namespace App\Modules\Users\Models\Fields;

use App\Halcyon\Form\Field;
use App\Modules\Users\Models\User as UserModel;

/**
 * Field to select a user id from a modal list.
 */
class User extends Field
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'User';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$groups = $this->getGroups();
		$excluded = $this->getExcluded();
		$link = route('api.users.index', ['field' => $this->id]);
			. (isset($groups) ? ('&amp;groups=' . base64_encode(json_encode($groups))) : '')
			. (isset($excluded) ? ('&amp;excluded=' . base64_encode(json_encode($excluded))) : '');

		// Initialize some field attributes.
		$attr  = 'class="form-control' . ($this->element['class'] ? ' ' . (string) $this->element['class'] : '') . '"';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';

		// Initialize JavaScript field attributes.
		$onchange = (string) $this->element['onchange'];

		// Load the modal behavior script.
		//Behavior::modal('a.modal_' . $this->id);

		// Build the script.
		$script = array();
		$script[] = '	function SelectUser_' . $this->id . '(id, title) {';
		$script[] = '		var old_id = document.getElementById("' . $this->id . '_id").value;';
		$script[] = '		if (old_id != id) {';
		$script[] = '			document.getElementById("' . $this->id . '_id").value = id;';
		$script[] = '			document.getElementById("' . $this->id . '_name").value = title;';
		$script[] = '			' . $onchange;
		$script[] = '		}';
		$script[] = '		$.fancybox.close();';
		$script[] = '	}';

		// Add the script to the document head.
		//app('document')->addScriptDeclaration(implode("\n", $script));

		// Load the current username if available.
		$name = '';
		if ($this->value)
		{
			$model = UserModel::find($this->value);
			$name = $model ? $model->name : '';
		}

		// Create a dummy text field with the user name.
		if ($this->element['readonly'] != 'true')
		{
			$html[] = '<span class="input-group">';
		}
		$html[] = '	<input type="text" id="' . $this->id . '_name" placeholder="' . trans('users::users.select user') . '" value="' . htmlspecialchars($name, ENT_COMPAT, 'UTF-8') . '" disabled="disabled"' . $attr . ' />';

		// Create the user select button.
		if ($this->element['readonly'] != 'true')
		{
			$html[] = '<span class="input-group-append">';
			$html[] = '		<a class="btn modal_' . $this->id . '" title="' . trans('users::users.change user') . '"' . ' href="' . $link . '" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><span class="input-group-text icon-user"></span>' . trans('users::users.change user') . '</a>';
			$html[] = '</span>';
			$html[] = '</span>';
		}

		// Create the real field, hidden, that stored the user id.
		$html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . (int) $this->value . '" />';

		return implode("\n", $html);
	}

	/**
	 * Method to get the filtering groups (null means no filtering)
	 *
	 * @return  mixed  array of filtering groups or null.
	 */
	protected function getGroups()
	{
		return null;
	}

	/**
	 * Method to get the users to exclude from the list of users
	 *
	 * @return  mixed  Array of users to exclude or null to to not exclude them
	 */
	protected function getExcluded()
	{
		return null;
	}
}
