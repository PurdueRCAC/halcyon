<?php

namespace App\Modules\Pages\Models\Fields;

use App\Halcyon\Form\Fields\Select;
use App\Halcyon\Html\Builder\Select as Dropdown;
use App\Modules\Pages\Models\Page as PageModel;

/**
 * Supports a modal article picker.
 */
class Page extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'Page';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  array
	 */
	protected function getOptions()
	{
		$options = PageModel::query()
			//->select(['id AS value', 'title AS text', 'level'])
			//->where('level', '>', 0)
			->where('state', '=', 1)
			->orderBy('path', 'asc')
			->get();

		$options->each(function ($page, $key)
		{
			$page->indent = str_repeat('|&mdash; ', $page->level);
			$page->text  = $page->indent . e($page->title);
			$page->value = $page->id;
		});

		if ($this->element['option_blank'])
		{
			$options->prepend(Dropdown::option('', trans('- Select -'), 'value', 'text'));
		}

		return $options; //->toArray();
	}

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		//$attr = '';

		$attributes = array(
			'name'         => $this->name,
			'id'           => $this->id,
			'size'         => ($this->element['size']      ? (int) $this->element['size']      : ''),
			'multiple'     => ($this->element['multiple'] ? 'multiple' : ''),
			'class'        => 'form-control' . ($this->element['class'] ? (string) ' ' . $this->element['class'] : ''),
			'readonly'     => ((string) $this->element['readonly'] == 'true'    ? 'readonly' : ''),
			'disabled'     => ((string) $this->element['disabled'] == 'true'    ? 'disabled' : ''),
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

		// Get the field options.
		$options = $this->getOptions();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->element['readonly'] == 'true')
		{
			$html[] = self::genericlist($options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" />';
		}
		// Create a regular list.
		else
		{
			$html[] = self::genericlist($options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);

			if ($this->element['option_other'])
			{
				$found = false;

				foreach ($options as $option)
				{
					if ($option->value == $this->value)
					{
						$found = true;
					}
				}
				$html[] = '<input type="text" name="' . $this->getName($this->fieldname . '_other') . '" value="' . ($found ? '' : htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8')) . '" placeholder="' . (empty($this->placeholder) ?  trans('global.other') : htmlspecialchars($this->placeholder, ENT_COMPAT, 'UTF-8')) . '" />';
			}
		}

		return implode($html);
	}

	/**
	 * Generates an HTML selection list.
	 *
	 * @param   array    $data       An array of objects, arrays, or scalars.
	 * @param   string   $name       The value of the HTML name attribute.
	 * @param   mixed    $attribs    Additional HTML attributes for the <select> tag. This
	 *                               can be an array of attributes, or an array of options. Treated as options
	 *                               if it is the last argument passed. Valid options are:
	 *                               Selection options
	 *                               list.attr, string|array: Additional attributes for the select
	 *                               element.
	 *                               id, string: Value to use as the select element id attribute.
	 *                               Defaults to the same as the name.
	 *                               list.select, string|array: Identifies one or more option elements
	 *                               to be selected, based on the option key values.
	 * @param   string   $optKey     The name of the object variable for the option value. If
	 *                               set to null, the index of the value array is used.
	 * @param   string   $optText    The name of the object variable for the option text.
	 * @param   mixed    $selected   The key that is selected (accepts an array or a string).
	 * @param   mixed    $idtag      Value of the field id or null by default
	 * @param   bool     $translate  True to translate
	 * @return  string   HTML for the select list.
	 */
	public static function genericlist($data, $name, $attribs = null, $optKey = 'value', $optText = 'text', $selected = null, $idtag = false, $translate = false)
	{
		// Set default options
		$options = array('id' => false);

		if (is_array($attribs) && func_num_args() == 3)
		{
			// Assume we have an options array
			$options = array_merge($options, $attribs);
		}
		else
		{
			// Get options from the parameters
			$options['id']             = $idtag;
			$options['list.attr']      = $attribs;
			$options['list.translate'] = $translate;
			$options['option.key']     = $optKey;
			$options['option.text']    = $optText;
			$options['list.select']    = $selected;
		}

		$attribs = '';
		if (isset($options['list.attr']))
		{
			if (is_array($options['list.attr']))
			{
				$attribs = self::toString($options['list.attr']);
			}
			else
			{
				$attribs = $options['list.attr'];
			}

			if ($attribs != '')
			{
				$attribs = ' ' . $attribs;
			}
		}

		$id = $options['id'] !== false ? $options['id'] : $name;
		$id = str_replace(array('[', ']'), '', $id);

		$html  = '<select' . ($id !== '' ? ' id="' . $id . '"' : '') . ' name="' . $name . '"' . $attribs . '>';
		foreach ($data as $item)
		{
			$sel = '';
			if ($item->{$optKey} == $selected)
			{
				$sel = ' selected="selected"';
			}
			if (!isset($item->path))
			{
				$item->path = '';
			}
			if (!isset($item->indent))
			{
				$item->indent = '';
			}
			$item->path = $item->path == '/' ? '' : $item->path;
			$html .= '<option value="' . $item->{$optKey} . '" data-indent="' . $item->indent . '" data-path="/' . $item->path . '"' . $sel . '>' . $item->{$optText} . '</option>';
		}
		$html .= '</select>';

		return $html;
	}
}
