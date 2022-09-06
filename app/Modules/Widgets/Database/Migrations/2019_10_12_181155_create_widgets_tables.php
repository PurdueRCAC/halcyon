<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateWidgetsTables extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('widgets'))
		{
			Schema::create('widgets', function (Blueprint $table)
			{
				$table->increments('id');
				$table->string('title', 100);
				$table->string('note', 255)->nullable();
				$table->text('content')->nullable();
				$table->integer('ordering')->unsigned()->default(0);
				$table->string('position', 50);
				$table->integer('checked_out')->unsigned()->default(0)->comment('FK to users.id');
				$table->dateTime('checked_out_time')->nullable();
				$table->dateTime('publish_up')->nullable();
				$table->dateTime('publish_down')->nullable();
				$table->tinyInteger('published')->unsigned()->default(0);
				$table->string('widget', 50);
				$table->integer('access')->unsigned()->default(0);
				$table->tinyInteger('showtitle')->unsigned()->default(0);
				$table->text('params')->nullable();
				$table->tinyInteger('client_id')->unsigned()->default(0);
				$table->string('language', 7);
				$table->index(['published', 'access'], 'published');
				$table->index(['widget', 'published'], 'widget');
				$table->index('language');
				$table->index('client_id');
			});
		}

		if (Schema::hasTable('extensions'))
		{
			$entries = DB::table('extensions')
				->where('type', '=', 'widget')
				->count();

			if (!$entries)
			{
				foreach (app('files')->directories(app_path('Widgets')) as $dir)
				{
					$element = basename($dir);
					$name = strtolower($element);
					$client_id = 0;

					$manifest = $dir . '/widget.json';
					if (file_exists($manifest))
					{
						$info = json_decode(file_get_contents($manifest));
						$name = $info->name;
						$client_id = ($info->type == 'admin' ? 1 : 0);
					}

					DB::table('extensions')->insert([
						'name'       => $name,
						'element'    => $element,
						'type'       => 'widget',
						'enabled'    => 1,
						'protected'  => 1,
						'state'      => 1,
						'access'     => 1,
						'client_id'  => $client_id,
					]);
				}
			}
		}

		if (Schema::hasTable('widgets'))
		{
			$total = DB::table('widgets')
				->count();

			if (!$total)
			{
				// Create the root node
				DB::table('widgets')->insert([
					'title' => 'Admin Menu',
					'note' => 'Admin menu',
					'widget' => 'Adminmenu',
					'position' => 'menu',
					'published' => 1,
					'ordering' => 1,
					'showtitle' => 0,
					'access' => 2,
					'client_id' => 1,
					'language' => '*',
				]);

				DB::table('widgets')->insert([
					'title' => 'Admin Menu',
					'note' => 'Admin menu',
					'widget' => 'Adminmenu',
					'position' => 'top',
					'published' => 1,
					'ordering' => 1,
					'showtitle' => 0,
					'access' => 2,
					'client_id' => 0,
					'language' => '*',
				]);

				DB::table('widgets')->insert([
					'title' => 'Main Menu',
					'note' => 'Main menu',
					'widget' => 'Menu',
					'position' => 'mainmenu',
					'published' => 1,
					'ordering' => 1,
					'showtitle' => 0,
					'access' => 1,
					'client_id' => 0,
					'language' => '*',
				]);

				DB::table('widgets')->insert([
					'title' => 'Breadcrumbs',
					'note' => 'breadcrumbs',
					'widget' => 'Breadcrumbs',
					'position' => 'breadcrumbs',
					'published' => 1,
					'ordering' => 1,
					'showtitle' => 0,
					'access' => 2,
					'client_id' => 1,
					'language' => '*',
					'params' => '{"showHere":0}',
				]);

				DB::table('widgets')->insert([
					'title' => 'Breadcrumbs',
					'note' => 'breadcrumbs',
					'widget' => 'Breadcrumbs',
					'position' => 'breadcrumbs',
					'published' => 1,
					'ordering' => 2,
					'showtitle' => 0,
					'access' => 1,
					'client_id' => 0,
					'language' => '*',
					'params' => '{"showHere":0}',
				]);
			}
		}

		if (!Schema::hasTable('widgets_menu'))
		{
			Schema::create('widgets_menu', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('widgetid')->unsigned()->default(0)->comment('FK to widgets.id');
				$table->integer('menuid')->unsigned()->default(0)->comment('FK to menus.id');
			});

			DB::table('widgets_menu')->insert([
				'widgetid' => 1,
				'menuid' => 0,
			]);
			DB::table('widgets_menu')->insert([
				'widgetid' => 2,
				'menuid' => 0,
			]);
			DB::table('widgets_menu')->insert([
				'widgetid' => 3,
				'menuid' => 0,
			]);
			DB::table('widgets_menu')->insert([
				'widgetid' => 4,
				'menuid' => 0,
			]);
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('widgets');
		Schema::dropIfExists('widgets_menu');
	}
}
