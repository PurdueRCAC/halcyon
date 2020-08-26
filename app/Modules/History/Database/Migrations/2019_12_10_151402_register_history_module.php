<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RegisterHistoryModule extends Migration
{
	/**
	 * Module name
	 *
	 * @var  string
	 */
	private static $name = 'history';

	/**
	 * Run the migrations.
	 *
	 * @return null
	 */
	public function up()
	{
		if (Schema::hasTable('extensions'))
		{
			$found = DB::table('extensions')
				->where('type', '=', 'module')
				->where('element', '=', self::$name)
				->get()
				->first();

			if (!$found || !$found->id)
			{
				$id = DB::table('extensions')
					->insertGetId([
						'type'      => 'module',
						'name'      => self::$name,
						'element'   => self::$name,
						'enabled'   => 1,
						'access'    => 1,
						'protected' => 1
					]);

				if ($id)
				{
					$id = DB::table('menu')
						->insert([
							'menutype'  => 'main',
							'type'      => 'module',
							'title'     => self::$name,
							'alias'     => self::$name,
							'path'      => self::$name,
							'module_id' => $id,
							'access'    => 1,
							'published' => 1,
							'client_id' => 1,
						]);
				}
			}
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return null
	 */
	public function down()
	{
		if (Schema::hasTable('extensions'))
		{
			DB::table('extensions')
				->where('type', '=', 'module')
				->where('element', '=', self::$name)
				->delete();

			DB::table('menu')
				->where('client_id', '=', 1)
				->where('type', '=', 'module')
				->where('alias', '=', self::$name)
				->delete();
		}
	}
}
