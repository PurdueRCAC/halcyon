<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Widgets\Models\Fields;

use App\Halcyon\Form;
use App\Halcyon\Form\Fields\Select;
use App\Halcyon\Base\ClientManager;
use App\Modules\Widgets\Models\Widget;
use Illuminate\Support\Facades\DB;

/**
 * Supports a modal article picker.
 */
class WidgetPosition extends select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'WidgetPosition';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Get the client id.
		$clientId = $this->element['client_id'];
		if (!isset($clientId))
		{
			$clientName = $this->element['client'];
			if (isset($clientName))
			{
				$client = ClientManager::client($clientName, true);
				$clientId = $client->id;
			}
		}
		if (!isset($clientId) && $this->form instanceof Form)
		{
			$clientId = $this->form->getValue('client_id');
		}
		$clientId = (int) $clientId;

		// Load the modal behavior script.
		//Html::behavior('modal', 'a.modal');

		// Build the script.
		$script = array();
		$script[] = '	function jSelectPosition_'.$this->id.'(name) {';
		$script[] = '		$("#'.$this->id.'").val(name);';
		$script[] = '		$.fancybox.close();';
		$script[] = '	}';

		// Add the script to the document head.
		//Document::addScriptDeclaration(implode("\n", $script));

		// Setup variables for display.
		$html = array();
		$link = route('admin.widgets.positions', ['client' => $clientId]);

		// The current user display field.
		//$html[] = '<div class="fltlft">';
		/*$html[] = '<div class="input-group">';
		$html[] = parent::getInput();

		// The user select button.
		$html[] = '<div class="input-group-append">';
		$html[] = '<a class="btn btn-secondary" href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">'.trans('widgets::widgets.CHANGE_POSITION_BUTTON').'</a>';
		$html[] = '</div>';

		$html[] = '</div>';*/
		$html[] = parent::getInput();
		$html[] = '<script>' . implode("\n", $script) . '</script>';

		return implode("\n", $html);
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getOptions()
	{
		$positions = Widget::query()
			->select(DB::raw('DISTINCT(position)'))
			->get()
			->pluck('position')
			->toArray();

		foreach ($positions as $position)
		{
			$options[] = array(
				'value' => $position,
				'text' => $position
			);
		}

		return $options;
	}
}
