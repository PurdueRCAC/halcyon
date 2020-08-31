<?php

namespace App\Modules\Menus\Http\Middleware;

use Closure;

class SetActiveMenu
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string|null  $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next, $guard = null)
	{
		if (!app()->has('menu') || app()->runningInConsole())
		{
			return $next($request);
		}

		$route = $request->path(); //app('router')->current();
		$route = trim($route, '/');

		$menu = app('menu');

		if (empty($route))
		{
			$item = $menu->getDefault();

			// if user not allowed to see default menu item then avoid notices
			if (is_object($item))
			{
				// Set the information in the request
				//$vars = $item->query;

				// Get the itemid
				//$vars['menuid'] = $item->id;
				$request->merge(['itemid' => $item->id]);

				// Set the active menu item
				$menu->setActive($item->id);
			}

			return $next($request);
		}

		$found = false;
		$route = strtolower($route);
		$lang  = app('translator')->locale();
		$items = array_reverse($menu->getMenu());

		foreach ($items as $item)
		{
			//sqlsrv  change
			if (isset($item->language))
			{
				$item->language = trim($item->language);
			}

			// Keep searching for better matches with higher depth
			$depth = substr_count(trim($item->path, '/'), '/') + 1;

			// Get the length of the route
			$length = strlen($item->link);
			$item->link = trim($item->link, '/');
//echo $route . '/' . ' -- '. $item->link . '/<br />';
			if ($length > 0 && strpos($route . '/', $item->link . '/') === 0
			 && $item->type != 'alias'
			 && (!app()->has('language.filter') || $item->language == '*' || $item->language == $lang))
			{
				// Handle external url menu items differently
				if ($item->type == 'url')
				{
					// If menu route exactly matches url route, redirect (if necessary) to menu link
					if (trim($item->link, '/') == trim($route, '/'))
					{
						/*if (trim($item->route, '/') != trim($item->link, '/')
						 && trim(url('/') . '/' . $item->route, '/') != trim($item->link, '/') // Added because it would cause redirect loop for installs not in top-level webroot
						 && trim(url('/') . '/index.php/' . $item->route, '/') != trim($item->link, '/')) // Added because it would cause redirect loop for installs not in top-level webroot
						{
							\App::redirect($item->link);
						}*/
						//$menu->setActive($item->id);
						$found = $item;
						break;
					}

					// Pass local URLs through, but record Itemid (we want the content parser to handle this)
					if (strpos($item->link, '://') === false)
					{
						//$vars['Itemid'] = $item->id;
						//$menu->setActive($item->id);
						$found = $item;
						break;
					}
				}

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

		// No menu item found.
		// Carry on...
		if ($found)
		{
			/*$route = substr($route, strlen($found->route));
			if ($route)
			{
				$route = substr($route, 1);
			}

			$uri->setVar('Itemid', $found->id);
			$uri->setVar('option', $found->component);
			$uri->setPath($route);
			foreach ($found->query as $key => $val)
			{
				$uri->setVar($key, $val);
			}*/
			$request->merge(['itemid' => $found->id]);

			$menu->setActive($found->id);
		}

		return $next($request);
	}
}
