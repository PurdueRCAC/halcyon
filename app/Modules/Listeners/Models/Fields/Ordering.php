<?php

namespace App\Modules\Listeners\Models\Fields;

use App\Halcyon\Form\Field;
use App\Halcyon\Html\Builder\Select;
use App\Modules\Listeners\Models\Listener;

/**
 * Supports an HTML select list of plugins
 */
class Ordering extends Field
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Ordering';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		// Get some field values from the form.
		$pluginId = (int) $this->form->getValue('id');
		$folder = $this->form->getValue('folder');

		$listeners = Listener::query()
			->select(['id', 'ordering AS value', 'name AS text', 'type', 'folder'])
			->where('type', 'listener')
			->where('folder', $folder)
			->orderBy('ordering', 'asc')
			->get();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->element['readonly'] == 'true')
		{
			$html[] = self::ordering('', $query, trim($attr), $this->value, $pluginId ? 0 : 1);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '" />';
		}
		// Create a regular list.
		else
		{
			$html[] = self::ordering($this->name, $query, trim($attr), $this->value, $pluginId ? 0 : 1);
		}

		return implode($html);
	}

	/**
	 * Returns an array of options
	 *
	 * @param   string   $sql  	SQL with 'ordering' AS value and 'name field' AS text
	 * @param   int  $chop  The length of the truncated headline
	 * @return  array    An array of objects formatted for JHtml list processing
	 */
	public static function genericordering($items, $chop = '30')
	{
		$options = array();

		if (empty($items))
		{
			$options[] = Select::option(1, trans('global.option.order first'));
			return $options;
		}

		$options[] = Select::option(0, '0 ' . trans('global.option.order first'));
		foreach ($items as $i => $item)
		{
			$item->text = trans($item->text);
			if (strlen($item->text) > $chop)
			{
				$text = substr($items[$i]->text, 0, $chop) . '...';
			}
			else
			{
				$text = $items[$i]->text;
			}

			$options[] = Select::option($items[$i]->value, $items[$i]->value . '. ' . $text);
		}
		$options[] = Select::option($items[$i - 1]->value + 1, ($items[$i - 1]->value + 1) . ' ' . trans('global.option.order last'));

		return $options;
	}

	/**
	 * Build the select list for Ordering derived from a query
	 *
	 * @param   int  $name      The scalar value
	 * @param   string   $query     The query
	 * @param   string   $attribs   HTML tag attributes
	 * @param   string   $selected  The selected item
	 * @param   int  $neworder  1 if new and first, -1 if new and last, 0  or null if existing item
	 * @param   string   $chop      The length of the truncated headline
	 * @return  string   Html for the select list
	 */
	public static function ordering($name, $items, $attribs = null, $selected = null, $neworder = null, $chop = null)
	{
		if (empty($attribs))
		{
			$attribs = 'class="inputbox" size="1"';
		}

		if (empty($neworder))
		{
			$orders = self::genericordering($items);
			$html = Select::genericlist($orders, $name, array('list.attr' => $attribs, 'list.select' => (int) $selected));
		}
		else
		{
			if ($neworder > 0)
			{
				$text = trans('global.new items last');
			}
			elseif ($neworder <= 0)
			{
				$text = trans('global.new items first');
			}
			$html = '<input type="hidden" name="' . $name . '" value="' . (int) $selected . '" />' . '<span class="readonly">' . $text . '</span>';
		}
		return $html;
	}
}
