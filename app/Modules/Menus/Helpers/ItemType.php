<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Menus\Helpers;

use App\Modules\Menus\Helpers\Menus as MenusHelper;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\File;
use stdClass;

/**
 * Menu type model
 */
class ItemType
{
	/**
	 * A reverse lookup of the base link URL to Title
	 *
	 * @var  array
	 */
	protected $rlu = array();

	/**
	 * Method to get the reverse lookup of the base link URL to Title
	 *
	 * @return  array  Array of reverse lookup of the base link URL to Title
	 */
	public function getReverseLookup()
	{
		if (empty($this->rlu))
		{
			$this->getTypeOptions();
		}
		return $this->rlu;
	}

	/**
	 * Method to get the available menu item type options.
	 *
	 * @return  array  Array of groups with menu item types.
	 */
	public function getTypeOptions()
	{
		$list = array();

		// Get the list of components.
		$modules = Module::getByStatus(1);

		foreach ($modules as $module)
		{
			if ($options = $this->getTypeOptionsByModule($module))
			{
				$list[$module->getName()] = $options;

				// Create the reverse lookup for link-to-name.
				foreach ($options as $option)
				{
					if (isset($option->route))
					{
						//$this->rlu[MenusHelper::getLinkKey($option->request)] = $option->title;
						$this->rlu[$option->route] = $option->title;

						//$module->registerLanguage();
					}
				}
			}
		}

		return $list;
	}

	/**
	 * Method to get type options by module
	 *
	 * @param   object  $module
	 * @return  array
	 */
	protected function getTypeOptionsByModule($module)
	{
		// Initialise variables.
		$options = array();

		$mainXML = module_path($module->getName()) . '/Resources/views/site/metadata.xml';

		if (is_file($mainXML))
		{
			$options = $this->getTypeOptionsFromXML($mainXML, $module);
		}

		if (empty($options))
		{
			$options = $this->getTypeOptionsFromMVC($module);
		}

		return $options;
	}

	/**
	 * Method to get type options from XML
	 *
	 * @param   string  $file
	 * @param   string  $module
	 * @return  array
	 */
	protected function getTypeOptionsFromXML($file, $module)
	{
		// Initialise variables.
		$options = array();

		// Attempt to load the xml file.
		if (!$xml = simplexml_load_file($file))
		{
			return false;
		}

		// Look for the first menu node off of the root node.
		if (!$menu = $xml->xpath('menu[1]'))
		{
			return false;
		}
		else
		{
			$menu = $menu[0];
		}

		// If we have no options to parse, just add the base component to the list of options.
		if (!empty($menu['options']) && $menu['options'] == 'none')
		{
			// Create the menu option for the component.
			$o = new stdClass;
			$o->title       = (string) $menu['name'];
			$o->description = (string) $menu['msg'];
			$o->request     = array('option' => $module);

			$options[] = $o;

			return $options;
		}

		// Look for the first options node off of the menu node.
		if (!$optionsNode = $menu->xpath('options[1]'))
		{
			return false;
		}
		else
		{
			$optionsNode = $optionsNode[0];
		}

		// Make sure the options node has children.
		if (!$children = $optionsNode->children())
		{
			return false;
		}
		else
		{
			// Process each child as an option.
			foreach ($children as $child)
			{
				if ($child->getName() == 'option')
				{
					// Create the menu option for the module.
					$o = new stdClass;
					$o->title       = (string) $child['name'];
					$o->description = (string) $child['msg'];
					$o->request     = array('option' => $module, (string) $optionsNode['var'] => (string) $child['value']);

					$options[] = $o;
				}
				elseif ($child->getName() == 'default')
				{
					// Create the menu option for the module.
					$o = new stdClass;
					$o->title       = (string) $child['name'];
					$o->description = (string) $child['msg'];
					$o->request     = array('option' => $module);

					$options[] = $o;
				}
			}
		}

		return $options;
	}

	/**
	 * Method to get type options from MVC
	 *
	 * @param   object  $module
	 * @return  array
	 */
	protected function getTypeOptionsFromMVC($module)
	{
		// Initialise variables.
		$options = array();

		// Get the views for this component.
		$path = module_path($module->getName()) . '/Resources/views/site';

		if (!is_dir($path))
		{
			return false;
		}

		//$layouts = File::glob($path . '/*.xml');
		$options = array_merge($options, (array) $this->getTypeOptionsFromLayouts($module));

		$views = File::directories($path);

		foreach ($views as $view)
		{
			$view = trim($view, '/');

			// Ignore private views.
			if (strpos($view, '_') !== 0)
			{
				// Determine if a metadata file exists for the view.
				$file = $path . '/' . $view . '/metadata.xml';

				if (is_file($file))
				{
					// Attempt to load the xml file.
					if ($xml = simplexml_load_file($file))
					{
						// Look for the first view node off of the root node.
						if ($menu = $xml->xpath('view[1]'))
						{
							$menu = $menu[0];

							// If the view is hidden from the menu, discard it and move on to the next view.
							if (!empty($menu['hidden']) && $menu['hidden'] == 'true')
							{
								unset($xml);
								continue;
							}

							// Do we have an options node or should we process layouts?
							// Look for the first options node off of the menu node.
							if ($optionsNode = $menu->xpath('options[1]'))
							{
								$optionsNode = $optionsNode[0];

								// Make sure the options node has children.
								if ($children = $optionsNode->children())
								{
									// Process each child as an option.
									foreach ($children as $child)
									{
										if ($child->getName() == 'option')
										{
											// Create the menu option for the component.
											$o = new stdClass;
											$o->title       = (string) $child['name'];
											$o->description = (string) $child['msg'];
											$o->request     = array(
												'option' => $module->getName(),
												'view'   => $view,
												(string) $optionsNode['var'] => (string) $child['value']
											);

											$options[] = $o;
										}
										elseif ($child->getName() == 'default')
										{
											// Create the menu option for the component.
											$o = new stdClass;
											$o->title       = (string) $child['name'];
											$o->description = (string) $child['msg'];
											$o->request     = array(
												'option' => $module->getName(),
												'view'   => $view
											);

											$options[] = $o;
										}
									}
								}
							}
							else
							{
								$options = array_merge($options, (array) $this->getTypeOptionsFromLayouts($module, $view));
							}
						}
						unset($xml);
					}
				}
				else
				{
					$options = array_merge($options, (array) $this->getTypeOptionsFromLayouts($module, $view));
				}
			}
		}

		return $options;
	}

	/**
	 * Method to get type options from layouts
	 *
	 * @param   object  $module
	 * @param   string  $view
	 * @return  array
	 */
	protected function getTypeOptionsFromLayouts($module, $view = null)
	{
		// Initialise variables.
		$options = array();
		$layouts = array();
		$layoutNames = array();
		$templateLayouts = array();

		// Get the layouts from the view folder.
		$path = module_path($module->getName()) . '/Resources/views/site' . ($view ? '/' . $view : '');

		if (!file_exists($path))
		{
			return $options;
		}

		$layouts = File::glob($path . '/*.xml');

		// build list of standard layout names
		foreach ($layouts as $layout)
		{
			//$layout = basename($layout);
			$layout = trim($layout, '/');

			// Ignore private layouts.
			if (strpos(basename($layout), '_') === false)
			{
				$file = $layout;

				// Get the layout name.
				$layoutNames[] = preg_replace('#\.[^.]*$#', '', basename($layout));
			}
		}

		// Process the found layouts.
		foreach ($layouts as $layout)
		{
			$layout = rtrim($layout, '/');

			// Ignore private layouts.
			if (strpos(basename($layout), '_') === false)
			{
				$file = $layout;

				// Get the layout name.
				$layout = preg_replace('#\.[^.]*$#', '', basename($layout));

				// Create the menu option for the layout.
				$o = new stdClass;
				$o->title       = ucfirst($layout);
				$o->description = '';
				$o->request     = array(
					'module' => $module->getName(),
					'view'   => $view
				);
				$o->route = 'site.' . $module->getLowerName() . '.' . $layout;

				// Only add the layout request argument if not the default layout.
				if ($layout != 'default')
				{
					// If the template is set, add in format template:layout so we save the template name
					$o->request['layout'] = $layout; //(isset($templateName[$file])) ? $templateName[$file] . ':' . $layout : $layout;
				}

				// Load layout metadata if it exists.
				if (is_file($file))
				{
					// Attempt to load the xml file.
					if ($xml = simplexml_load_file($file))
					{
						// Look for the first view node off of the root node.
						if ($menu = $xml->xpath('layout[1]'))
						{
							$menu = $menu[0];

							// If the view is hidden from the menu, discard it and move on to the next view.
							if (!empty($menu['hidden']) && $menu['hidden'] == 'true')
							{
								unset($xml);
								unset($o);
								continue;
							}

							// Populate the title and description if they exist.
							if (!empty($menu['title']))
							{
								$o->title = trim((string) $menu['title']);
							}

							if (!empty($menu->message[0]))
							{
								$o->description = trim((string) $menu->message[0]);
							}

							if (!empty($menu['route']))
							{
								$o->route = trim((string) $menu['route']);
							}
						}
					}
				}

				// Add the layout to the options array.
				$options[] = $o;
			}
		}

		return $options;
	}
}
