<?php

namespace App\Modules\Widgets\Models\Fields;

use App\Halcyon\Form\Field;
use App\Modules\Widgets\Models\Widget;

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

		$orders = Widget::query()
			->select('position', 'ordering', 'title')
			->where('client_id', '=', (int) $clientId)
			->orderBy('ordering', 'asc')
			->get();

		$orders2 = array();
		//for ($i = 0, $n = count($orders); $i < $n; $i++)
		foreach ($orders as $i => $order)
		{
			if (!isset($orders2[$order->position]))
			{
				$orders2[$order->position] = 0;
			}
			$orders2[$order->position]++;

			$ord = $orders2[$order->position];
			$title = trans('widgets::widgets.option.order position', [
				'order' => $ord,
				'title' => addslashes($order->title)
			]);

			$data->orders[$i] = array($order->position, $ord, $title);
		}

		$html = array();
		$html[] = '<script type="application/json" id="widgetorder">';
		$html[] = json_encode($data);
		$html[] = '</script>';

		return implode("\n", $html);
	}
}
