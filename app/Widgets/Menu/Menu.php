<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Widgets\Menu;

use App\Modules\Widgets\Entities\Widget;
use App\Modules\Menus\Entities\Menu as SiteMenu;

/**
 * Module class for displaying a menu
 */
class Menu extends Widget
{
	/**
	 * Display module
	 *
	 * @return  void
	 */
	public function run()
	{
		$menu = new SiteMenu([
			'access' => auth()->user() ? auth()->user()->getAuthorisedViewLevels() : array(1)
		]);

		$list      = self::getList($menu, $this->params);
		//$menu      = App::get('menu');

		$active    = $menu->getActive();
		$active_id = isset($active) ? $active->id : $menu->getDefault()->id;
		$path      = isset($active) ? $active->tree : array();
		$showAll   = $this->params->get('showAllChildren');
		$class_sfx = htmlspecialchars($this->params->get('class_sfx'));

		$layout = $this->params->get('layout');
		$layout = $layout ?: 'index';

		return view($this->getViewName($layout), [
			'list'      => $list,
			'path'      => $path,
			'active_id' => $active_id,
			'showAll'   => $showAll,
			'class_sfx' => $class_sfx,
			'params'    => $this->params,
		]);
	}

	/**
	 * Get a list of the menu items.
	 *
	 * @param   object  $params  Registry The module options.
	 * @return  array
	 */
	protected static function getList($menu, &$params)
	{
		//$menu = App::get('menu');

		// If no active menu, use default
		$active = ($menu->getActive()) ? $menu->getActive() : $menu->getDefault();

		/*$levels = User::getAuthorisedViewLevels();
		asort($levels);

		$key = 'mod_menu.' . 'menu_items' . $params . implode(',', $levels) . '.' . $active->id;

		if (!($items = Cache::get($key)))
		{*/
			// Initialise variables.
			$list     = array();
			$path     = $active->tree;
			$start    = (int) $params->get('startLevel');
			$end      = (int) $params->get('endLevel');
			$showAll  = $params->get('showAllChildren');
			$items    = $menu->getItems('menutype', $params->get('menutype'));

			$lastitem = 0;

			if ($items)
			{
				foreach ($items as $i => $item)
				{
					if (($start && $start > $item->level)
						|| ($end && $item->level > $end)
						//|| (!$showAll && $item->level > 1 && !in_array($item->parent_id, $path))
						|| ($start > 1 && !in_array($item->tree[$start-2], $path))
					)
					{
						unset($items[$i]);
						continue;
					}

					$item->deeper = false;
					$item->shallower = false;
					$item->level_diff = 0;

					if (isset($items[$lastitem]))
					{
						$items[$lastitem]->deeper     = ($item->level > $items[$lastitem]->level);
						$items[$lastitem]->shallower  = ($item->level < $items[$lastitem]->level);
						$items[$lastitem]->level_diff = ($items[$lastitem]->level - $item->level);
					}

					$item->parent = (boolean) $menu->getItems('parent_id', (int) $item->id, true);

					$lastitem = $i;
					$item->active = false;
					$item->flink  = $item->link;

					$item->title        = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
					$item->anchor_css   = htmlspecialchars($item->params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
					$item->anchor_title = htmlspecialchars($item->params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
					$item->menu_image   = $item->params->get('menu_image', '') ? htmlspecialchars($item->params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';

					// Reverted back for CMS version 2.5.6
					switch ($item->type)
					{
						case 'separator':
							// No further action needed.
							continue 2;
							break;

						case 'url':
							if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false))
							{
								// If this is an internal link, ensure the Itemid is set.
								$item->flink = $item->link . '&Itemid=' . $item->id;
							}
							break;

						case 'alias':
							// If this is an alias use the item id stored in the parameters to make the link.
							$item->flink = 'index.php?Itemid=' . $item->params->get('aliasoptions');
							break;

						default:
							//$item->flink = 'index.php?Itemid=' . $item->id;
							$item->flink = $item->link; //$item->path;
							break;
					}

					if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false))
					{
						$item->flink = url($item->flink, true, $item->params->get('secure'));
					}
					else
					{
						$item->flink = url($item->flink);
					}
				}

				if (isset($items[$lastitem]))
				{
					$items[$lastitem]->deeper     = (($start ? $start : 1) > $items[$lastitem]->level);
					$items[$lastitem]->shallower  = (($start ? $start : 1) < $items[$lastitem]->level);
					$items[$lastitem]->level_diff = ($items[$lastitem]->level - ($start ? $start : 1));
				}
			}

			/*Cache::put($key, $items, intval($params->get('cache_time', 900)) / 60);
		}*/

		return $items;
	}
}
