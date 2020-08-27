<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Menus\Listeners;

use Illuminate\Database\Events\MigrationEnded;
use App\Modules\Core\Models\Extension;
use App\Modules\Menus\Models\Item;
use App\Modules\Menus\Models\Type;

/**
 * Menu listener for sessions
 */
class InstallModule
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		//$events->listen(MigrationEnded::class, self::class . '@handleMigrationEnded');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
	 * @return  void
	 */
	public function handleMigrationEnded(MigrationEnded $event)
	{
		if (!isset($event->migration->module))
		{
			return;
		}

		$item = Item::query()
				->where('menutype', '=', 'main')
				->where('alias', '=', $event->migration->module)
				->get()
				->first();

		if ($event->method == 'up' && !$item)
		{
			$alias = $event->migration->module;

			$module = Extension::findModuleByName($alias);

			Item::create([
				'menutype'          => 'main',
				'title'             => $alias,
				'alias'             => $alias,
				'path'              => $alias,
				'link'              => route('admin.' . $alias . '.index'),
				'type'              => 'module',
				'published'         => 1,
				'parent_id'         => 1,
				'level'             => 1,
				'component_id'      => $module->id,
				'language'          => '*',
				'client_id'         => 1
			]);

			$menu = Type::findByMenutype('main');
			$menu->rebuild();

			$event->migration->info(sprintf('Added menu entry for module "%s"', $alias));
		}
		elseif ($event->method == 'down' && $item)
		{
			$item->delete();

			$event->migration->info(sprintf('Removed menu entry for module "%s"', $alias));
		}
	}
}
