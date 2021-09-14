<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration script for installing default themes
 **/
class CreateDefaultThemeEntries extends Migration
{
	/**
	 * Up
	 **/
	public function up()
	{
		if (Schema::hasTable('extensions'))
		{
			$themes = array(
				array(
					'type' => 'theme',
					'element' => 'admin',
					'name' => 'Admin (default)',
					'client_id' => 1,
					'enabled' => 1,
					'access' => 1,
					'protected' => 1,
					'params' => '{}',
				),
				array(
					'type' => 'theme',
					'element' => 'site',
					'name' => 'Site (default)',
					'client_id' => 0,
					'enabled' => 1,
					'access' => 1,
					'protected' => 1,
					'params' => '{}',
				),
			);

			foreach ($themes as $theme)
			{
				DB::table('extensions')->insert($theme);
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
			$themes = array(
				'admin',
				'site'
			);

			foreach ($themes as $theme)
			{
				DB::table('extensions')
					->where('type', '=', 'theme')
					->where('element', '=', $theme)
					->delete();
			}
		}
	}
}
