<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Widgets\Models\Fields;

use App\Halcyon\Form\Field;

/**
 * Form Field class for module ordering
 */
class WidgetOrder extends Field
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'WidgetOrder';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$attr = '';
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->element['onchange'] ? ' data-onchange="' . (string) $this->element['onchange'] . '"' : '';

		$ordering = $this->form->getValue('ordering');
		$position = $this->form->getValue('position');
		$clientId = $this->form->getValue('client_id');

		$data = new \stdClass;
		$data->originalOrder = $ordering;
		$data->originalPos = $position;
		$data->orders = array();
		$data->name = $this->name;
		$data->id = $this->id;
		$data->attr = $attr;

		$db = app('db');
		$orders = $db->table('widgets')
			->select('position', 'ordering', 'title')
			->where('client_id', '=', (int) $clientId)
			->orderBy('ordering', 'asc')
			->get();

		$orders2 = array();
		for ($i = 0, $n = count($orders); $i < $n; $i++)
		{
			if (!isset($orders2[$orders[$i]->position]))
			{
				$orders2[$orders[$i]->position] = 0;
			}
			$orders2[$orders[$i]->position]++;
			$ord = $orders2[$orders[$i]->position];
			$title = trans('widgets::widgets.option.order position', [
				'order' => $ord,
				'title' => addslashes($orders[$i]->title)
			]);

			$data->orders[$i] = array($orders[$i]->position, $ord, $title);
		}

		$html = array();
		$html[] = '<script type="application/json" id="widgetorder">';
		$html[] = json_encode($data);
		$html[] = '</script>';

		return implode("\n", $html);
	}
}
