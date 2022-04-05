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
	 **/
	public function up()
	{
		if (Schema::hasTable('extensions'))
		{
			// Register core module
			$entry = DB::table('extensions')
				->where('element', '=', 'core')
				->where('type', '=', 'module')
				->first();

			if (!$entry || !$entry->id)
			{
				DB::table('extensions')->insert([
					'name'      => 'core',
					'element'   => 'core',
					'type'      => 'module',
					'enabled'   => 1,
					'protected' => 1,
					'state'     => 1,
					'access'    => 1,
				]);
			}

			// Auto-register other modules
			$entries = DB::table('extensions')
				->where('element', '!=', 'core')
				->where('type', '=', 'module')
				->count();

			if (!$entries)
			{
				foreach (app('files')->directories(app_path('Modules')) as $dir)
				{
					$element = strtolower(basename($dir));

					if ($element == 'core')
					{
						continue;
					}

					DB::table('extensions')->insert([
						'name'       => $element,
						'element'    => $element,
						'type'       => 'module',
						'enabled'    => 1,
						'protected'  => (in_array($element, ['core', 'config', 'dashboard', 'history', 'users', 'listeners', 'widgets', 'pages', 'menus', 'media', 'tags', 'themes']) ? 1 : 0),
						'state'      => 1,
						'access'     => 1,
					]);
				}
			}
		}
	}

	/**
	 * Down
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
