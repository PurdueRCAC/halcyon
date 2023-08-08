<?php

namespace App\Modules\Core\Events;

use Spatie\Sitemap\Sitemap;

class GenerateSitemap
{
	/**
	 * Sitemap object
	 *
	 * @var Sitemap
	 */
	public $map;

	/**
	 * Constructor
	 *
	 * @param  Sitemap $map
	 * @return void
	 */
	public function __construct(Sitemap $map)
	{
		$this->map = $map;
	}
}
