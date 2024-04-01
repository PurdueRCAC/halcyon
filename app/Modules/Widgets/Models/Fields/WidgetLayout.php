<?php

namespace App\Modules\Widgets\Models\Fields;

use App\Halcyon\Form\Field;
use App\Halcyon\Form\Form;
use App\Halcyon\Html\Builder\Select as Dropdown;
use App\Halcyon\Filesystem\Util;
use App\Modules\Widgets\Models\Extension;

/**
 * Form Field to display a list of the layouts for widget display from the widget or template overrides.
 */
class WidgetLayout extends Field
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'WidgetLayout';

	/**
	 * Method to get the field input for widget layouts.
	 *
	 * @return  string  The field input.
	 */
	protected function getInput()
	{
		// Get the client id.
		$clientId = $this->element['client_id'];

		if (is_null($clientId) && $this->form instanceof Form)
		{
			$clientId = $this->form->getValue('client_id');
		}
		$clientId = (int) $clientId;

		// Get the widget.
		$widget = (string) $this->element['widget'];

		if (empty($widget) && ($this->form instanceof Form))
		{
			$widget = $this->form->getValue('widget');
		}

		$widget = preg_replace('#\W#', '', $widget);

		// Get the template.
		$template = (string) $this->element['template'];
		$template = preg_replace('#\W#', '', $template);

		// Get the style.
		if ($this->form instanceof Form)
		{
			$template_style_id = $this->form->getValue('template_style_id');
		}

		$template_style_id = preg_replace('#\W#', '', $template_style_id);

		// If an extension and view are present build the options.
		if ($widget)// && $client)
		{
			// Load language file
			app('translator')->addNamespace('widgets', app_path() . '/Widgets/' . $widget . '/lang');

			// Build the search paths for widget layouts.
			$widget_path = app_path() . '/Widgets/' . $widget . '/views';

			// Prepare array of component layouts
			$widget_layouts = array();

			// Prepare the grouped list
			$groups = array();

			// Add the layout options from the widget path.
			if (is_dir($widget_path) && ($widget_layouts = app('filesystem')->files($widget_path, '^[^_]*\.php$')))
			{
				// Create the group for the widget
				$groups['_'] = array();
				$groups['_']['id'] = $this->id . '__';
				$groups['_']['text'] = trans('widgets::widgets.from widget');
				$groups['_']['items'] = array();

				foreach ($widget_layouts as $file)
				{
					// Add an option to the widget group
					$value = app('filesystem')->name(ltrim($file, DIRECTORY_SEPARATOR));
					$text = app('translator')->has($key = strtoupper($widget . ' layout ' . $value)) ? trans($key) : $value;

					$groups['_']['items'][] = Dropdown::option('_:' . $value, $text);
				}
			}

			// Loop on all templates
			/*
			// Build the query.
			$query = Extension::query()
				->select('element', 'name')
				->where('client_id', '=', (int) $clientId)
				->where('type', '=', 'theme')
				->where('enabled', '=', '1');

			if ($template)
			{
				$query->where('element', '=', $template);
			}

			// Set the query and load the templates.
			$templates = $query->get();

			if (count($templates) > 0)
			{
				foreach ($templates as $template)
				{
					$template->path = '';

					foreach ($paths as $p)
					{
						if (is_dir($p . '/templates/' . $template->element))
						{
							$template->path = $p . '/templates/' . $template->element;
							break;
						}
					}

					if (!$template->path)
					{
						continue;
					}

					// Load language file
					$lang->load('tpl_' . $template->element . '.sys', $template->path, null, false, true);

					$template_path = Util::normalizePath($template->path . '/html/' . $widget);

					// Add the layout options from the template path.
					if (is_dir($template_path) && ($files = app('filesystem')->files($template_path, '^[^_]*\.php$')))
					{
						foreach ($files as $i => $file)
						{
							// Remove layout that already exist in component ones
							if (in_array($file, $widget_layouts))
							{
								unset($files[$i]);
							}
						}

						if (count($files))
						{
							// Create the group for the template
							$groups[$template->element] = array();
							$groups[$template->element]['id'] = $this->id . '_' . $template->element;
							$groups[$template->element]['text'] = $lang->txt('JOPTION_FROM_TEMPLATE', $template->name);
							$groups[$template->element]['items'] = array();

							foreach ($files as $file)
							{
								// Add an option to the template group
								$value = app('filesystem')->name(ltrim($file, DIRECTORY_SEPARATOR));
								$text = $lang->hasKey($key = strtoupper('TPL_' . $template->element . '_' . $widget . '_LAYOUT_' . $value))
									? $lang->txt($key)
									: $value;
								$groups[$template->element]['items'][] = Dropdown::option($template->element . ':' . $value, $text);
							}
						}
					}
				}
			}*/
			// Compute attributes for the grouped list
			$attr = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';

			// Prepare HTML code
			$html = array();

			// Compute the current selected values
			$selected = array($this->value);

			// Add a grouped list
			$html[] = Dropdown::groupedlist(
				$groups,
				$this->name,
				array(
					'id'          => $this->id,
					'group.id'    => 'id',
					'list.attr'   => $attr,
					'list.select' => $selected
				)
			);

			return implode($html);
		}

		return '';
	}
}
