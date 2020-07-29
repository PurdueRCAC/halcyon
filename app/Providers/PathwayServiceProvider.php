<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Providers;

use App\Http\Pathway\Trail;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * Breadcrumbs service provider
 */
class PathwayServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return  void
	 */
	public function register()
	{
		$this->app->singleton('pathway', function ($app)
		{
			return new Trail();
		});
	}

	/**
	 * Bootstrap the application pathway.
	 *
	 * @return void
	 */
	public function boot()
	{
		if (!$this->app->bound('menu'))
		{
			return;
		}

		$menu = $this->app['menu'];

		if ($item = $menu->getActive())
		{
			$menus = $menu->getMenu();
			$home  = $menu->getDefault();

			if (is_object($home) && ($item->id != $home->id))
			{
				foreach ($item->tree as $menupath)
				{
					$url = '';
					$link = $menu->getItem($menupath);

					switch ($link->type)
					{
						case 'separator':
							$url = null;
							break;

						case 'url':
							if ((strpos($link->link, 'index.php?') === 0) && (strpos($link->link, 'Itemid=') === false))
							{
								// If this is an internal link, ensure the Itemid is set.
								$url = $link->link . '&Itemid=' . $link->id;
							}
							else
							{
								$url = $link->link;
							}
							break;

						case 'alias':
							// If this is an alias use the item id stored in the parameters to make the link.
							$url = 'index.php?Itemid=' . $link->params->get('aliasoptions');
							break;

						default:
							$url = 'index.php?Itemid=' . $link->id;
							break;
					}

					$this->app['pathway']->append($menus[$menupath]->title, $url);
				}
			}
		}
	}
}
