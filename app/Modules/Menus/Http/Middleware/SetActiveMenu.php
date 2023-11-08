<?php

namespace App\Modules\Menus\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class SetActiveMenu
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  Request  $request
	 * @param  Closure  $next
	 * @param  string|null  $guard
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next, $guard = null)
	{
		if (!app()->has('menu') || app()->runningInConsole())
		{
			return $next($request);
		}

		$route = $request->path();
		$route = trim($route, '/');

		$menu = app('menu');

		if (empty($route))
		{
			$item = $menu->getDefault();

			// if user not allowed to see default menu item then avoid notices
			if (is_object($item))
			{
				// Set the information in the request
				$request->merge(['itemid' => $item->id]);

				// Set the active menu item
				$menu->setActive($item->id);
			}

			return $next($request);
		}

		$found = false;
		$foundDepth = 0;
		$route = strtolower($route);
		$lang  = app('translator')->locale();
		$items = array_reverse($menu->getMenu());

		foreach ($items as $item)
		{
			if (isset($item->language))
			{
				$item->language = trim($item->language);
			}

			// Keep searching for better matches with higher depth
			$depth = substr_count(trim($item->path, '/'), '/') + 1;

			// Get the length of the route
			$length = strlen($item->link);
			$item->link = trim($item->link, '/');

			if ($length > 0 && strpos($route . '/', $item->link . '/') === 0
			 && $item->type != 'alias'
			 && (!app()->has('language.filter') || $item->language == '*' || $item->language == $lang))
			{
				// Handle external url menu items differently
				//if ($item->type == 'url')
				//{
					// If menu route exactly matches url route, redirect (if necessary) to menu link
					if (trim($item->link, '/') == trim($route, '/'))
					{
						$found = $item;
						break;
					}

					// Pass local URLs through, but record Itemid (we want the content parser to handle this)
					/*if (strpos($item->link, '://') === false)
					{
						//$vars['Itemid'] = $item->id;
						//$menu->setActive($item->id);
						$found = $item;
						break;
					}*/
				//}

				// We have exact item for this language
				if ($item->language == $lang)
				{
					$found      = $item;
					// Track depth so we can replace with a better match later
					$foundDepth = $depth;
					break;
				}
				// Or let's remember an item for all languages
				elseif (!$found || $depth >= $foundDepth)
				{
					// Deeper or equal depth matches later on are prefered
					$found      = $item;
					// Track depth so we can replace with a better match later
					$foundDepth = $depth;
				}
			}
		}

		if ($found)
		{
			$request->merge(['itemid' => $found->id]);

			$menu->setActive($found->id);
		}

		return $next($request);
	}
}
