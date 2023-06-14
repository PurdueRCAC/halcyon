<?php

namespace App\Modules\Menus\Listeners;

use Illuminate\Events\Dispatcher;
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
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events)
	{
		//$events->listen(MigrationEnded::class, self::class . '@handleMigrationEnded');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   MigrationEnded $event
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
