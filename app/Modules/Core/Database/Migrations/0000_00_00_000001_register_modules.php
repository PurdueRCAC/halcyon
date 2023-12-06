<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration script for installing core modules
 **/
class RegisterModules extends Migration
{
	/**
	 * Up
	 *
	 * @return void
	 **/
	public function up()
	{
		if (Schema::hasTable('extensions'))
		{
			// Register core module
			$entry = DB::table('extensions')
				->whereIn('element', ['core', 'Core'])
				->where('type', '=', 'module')
				->first();

			if (!$entry || !$entry->id)
			{
				DB::table('extensions')->insert([
					'name'      => 'core',
					'element'   => 'Core',
					'type'      => 'module',
					'folder'    => 'system',
					'enabled'   => 1,
					'protected' => 1,
					'state'     => 1,
					'access'    => 1,
					'ordering'  => 1,
				]);
			}

			// Auto-register other modules
			$entries = DB::table('extensions')
				->where('element', '!=', 'core')
				->where('element', '!=', 'Core')
				->where('type', '=', 'module')
				->count();

			if (!$entries)
			{
				$ordering = 1;

				foreach (app('files')->directories(app_path('Modules')) as $dir)
				{
					$element = basename($dir);

					if ($element == 'Core')
					{
						continue;
					}

					switch ($element)
					{
						case 'Config':
						case 'Cron':
						case 'History':
						case 'Messages':
						case 'Dashboard':
							$folder = 'system';
						break;

						case 'Finder':
						case 'Issues':
						case 'Queues':
						case 'Resources':
						case 'Storage':
							$folder = 'resources';
						break;

						case 'Users':
						case 'Groups':
						case 'ContactReports':
						case 'Mailer':
							$folder = 'users';
						break;

						case 'Languages':
						case 'Listeners':
						case 'Widgets':
							$folder = 'extensions';
						break;

						case 'Knowledge':
						case 'Media':
						case 'News':
						case 'Pages':
						case 'Publications':
						case 'Tags':
							$folder = 'content';
						break;

						default:
							$folder = strtolower($element);
					}

					$ordering++;

					DB::table('extensions')->insert([
						'name'       => $element,
						'element'    => $element,
						'folder'     => $folder,
						'type'       => 'module',
						'enabled'    => 1,
						'protected'  => (in_array($element, ['Config', 'Dashboard', 'History', 'Users', 'Listeners', 'Widgets', 'Pages', 'Menus', 'Media', 'Tags', 'Themes']) ? 1 : 0),
						'state'      => 1,
						'access'     => 1,
						'ordering'   => $ordering,
					]);
				}
			}
		}
	}

	/**
	 * Down
	 *
	 * @return void
	 **/
	public function down()
	{
		if (Schema::hasTable('extensions'))
		{
			$entry = DB::table('extensions')
				->where('element', '=', 'core')
				->where('type', '=', 'module')
				->first();

			if ($entry)
			{
				DB::table('extensions')
					->where('id', '=', $entry->id)
					->delete();
			}

			// Auto-register other modules
			$entries = DB::table('extensions')
				->where('element', '!=', 'core')
				->where('type', '=', 'module')
				->count();

			if ($entries)
			{
				foreach (app('files')->directories(app_path('Modules')) as $dir)
				{
					$element = strtolower(basename($dir));

					if ($element == 'core')
					{
						continue;
					}

					DB::table('extensions')
						->where('element', '=', $element)
						->where('type', '=', 'module')
						->delete();
				}
			}
		}
	}
}
