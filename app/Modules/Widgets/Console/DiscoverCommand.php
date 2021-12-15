<?php

namespace App\Modules\Widgets\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DiscoverCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'widget:discover';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Discover new widgets to install.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$dirs = $this->laravel['files']->directories(app_path('Widgets'));

		foreach ($dirs as $dir)
		{
			$element = strtolower(basename($dir));

			$found = DB::table('extensions')
				->where('type', '=', 'widget')
				->where('element', '=', $element)
				->count();

			if ($found)
			{
				continue;
			}

			$name = $element;

			$manifest = $dir . '/widget.json';
			if (file_exists($manifest))
			{
				$info = json_decode(file_get_contents($manifest));
				$name = $info->name;
			}

			DB::table('extensions')->insert([
				'name'       => $name,
				'element'    => $element,
				'type'       => 'widget',
				'enabled'    => 1,
				'protected'  => 1,
				'state'      => 1,
				'access'     => 1,
			]);

			$this->info('Discovered widget: ' . $name . ' (' . $element . ')');
		}
	}
}
