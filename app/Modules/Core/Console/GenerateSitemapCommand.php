<?php

namespace App\Modules\Core\Console;

use Illuminate\Console\Command;
use App\Modules\Core\Events\GenerateSitemap;
use Spatie\Sitemap\Sitemap;

class GenerateSitemapCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'sitemap:generate';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate a sitemap.';

	/**
	 * Execute the actions
	 *
	 * @return void
	 */
	public function handle()
	{
		$map = Sitemap::create();

		event($event = new GenerateSitemap($map));
	
		$map = $event->map;
		$map->writeToFile(public_path('sitemap.xml'));
	}
}
