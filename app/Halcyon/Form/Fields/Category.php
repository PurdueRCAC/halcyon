<?php

namespace App\Halcyon\Form\Fields;

use App\Halcyon\Html\Builder\Category as Cat;
use App\Halcyon\Html\Builder\Select as Dropdown;

/**
 * Supports an HTML select list of categories
 */
class Category extends Select
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Category';

	/**
	 * Method to get the field options for category
	 * Use the extension attribute in a form to specify the.specific extension for
	 * which categories should be displayed.
	 * Use the show_root attribute to specify whether to show the global category root in the list.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		// Initialise variables.
		$options   = array();
		$extension = $this->element['extension'] ? (string) $this->element['extension'] : (string) $this->element['scope'];
		$published = (string) $this->element['published'];
		$name      = (string) $this->element['name'];

		// Load the category options for a given extension.
		if (!empty($extension))
		{
			// Filter over published state or not depending upon if it is present.
			if ($published)
			{
				$options = Cat::options($extension, array('filter.published' => explode(',', $published)));
			}
			else
			{
				$options = Cat::options($extension);
			}

			// Verify permissions.  If the action attribute is set, then we scan the options.
			if ((string) $this->element['action'])
			{

				// Get the current user object.
				$user = auth()->user();

				// For new items we want a list of categories you are allowed to create in.
				if (!$this->form->getValue($name))
				{
					foreach ($options as $i => $option)
					{
						// To take save or create in a category you need to have create rights for that category
						// unless the item is already in that category.
						// Unset the option if the user isn't authorised for it. In this field assets are always categories.
						if ($user->can('create ' . $extension . '.category.' . $option->value) != true)
						{
							unset($options[$i]);
						}
					}
				}
				// If you have an existing category id things are more complex.
				else
				{
					$categoryOld = $this->form->getValue($name);

					foreach ($options as $i => $option)
					{
						// If you are only allowed to edit in this category but not edit.state, you should not get any
						// option to change the category.
						if ($user->can('edit.state ' . $extension . '.category.' . $categoryOld) != true)
						{
							if ($option->value != $categoryOld)
							{
								unset($options[$i]);
							}
						}
						// However, if you can edit.state you can also move this to another category for which you have
						// create permission and you should also still be able to save in the current category.
						elseif (($user->can('create ' . $extension . '.category.' . $option->value) != true) && $option->value != $categoryOld)
						{
							unset($options[$i]);
						}
					}
				}
			}

			if (isset($this->element['show_root']))
			{
				array_unshift($options, Dropdown::option('0', trans('global.root')));
			}
		}
		else
		{
			abort(500, trans('global.error.category extension empty'));
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
