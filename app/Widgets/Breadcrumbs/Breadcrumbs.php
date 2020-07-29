<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Widgets\Breadcrumbs;

use App\Modules\Widgets\Entities\Widget;
use stdClass;

/**
 * Module class for displaying breadcrumbs
 */
class Breadcrumbs extends Widget
{
	/**
	 * Display module
	 *
	 * @return  void
	 */
	public function run()
	{
		// Get the breadcrumbs
		$list  = $this->getList();
		$count = count($list);

		// Set the default separator
		$separator = $this->setSeparator($this->params->get('separator'));
		$class_sfx = htmlspecialchars($this->params->get('moduleclass_sfx'));

		$layout = $this->params->get('layout');
		$layout = $layout ?: 'index';

		return view($this->getViewName($layout), [
			'list'      => $list,
			'count'     => $count,
			'separator' => $separator,
			'class_sfx' => $class_sfx,
			'params'    => $this->params
		]);
	}

	/**
	 * Get the list of crumbs
	 *
	 * @return  array
	 */
	public function getList()
	{
		if (!app()->bound('pathway'))
		{
			return array();
		}

		$items = app('pathway')->all();

		$count = count($items);

		// Don't use $items here as it references Pathway properties directly
		$crumbs = array();
		for ($i = 0; $i < $count; $i ++)
		{
			$crumbs[$i] = new stdClass();
			$crumbs[$i]->name = stripslashes(htmlspecialchars($items[$i]->name, ENT_COMPAT, 'UTF-8'));
			$crumbs[$i]->link = $items[$i]->link;
		}

		if ($this->params->get('showHome', 1))
		{
			$item = new stdClass();
			$item->name = htmlspecialchars($this->params->get('homeText', trans('widget.breadcrumbs::breadcrumbs.home')));
			$item->link = url('/'); //url('index.php?Itemid=' . app('menu')->getDefault()->id);

			array_unshift($crumbs, $item);
		}

		return $crumbs;
	}

	/**
	 * Set the breadcrumbs separator for the breadcrumbs display.
	 *
	 * @param   string  $custom  Custom xhtml complient string to separate the items of the breadcrumbs
	 * @return  string  Separator string
	 */
	public function setSeparator($custom = null)
	{
		// If a custom separator has not been provided we try to load a template
		// specific one first, and if that is not present we load the default separator
		if ($custom == null)
		{
			$_separator = '/';
		}
		else
		{
			$_separator = htmlspecialchars($custom);
		}

		return $_separator;
	}
}
