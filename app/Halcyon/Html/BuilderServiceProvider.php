<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Html;

use Illuminate\Support\ServiceProvider;

/**
 * HTML Helper service provider
 */
class BuilderServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return  void
	 */
	public function register()
	{
		$this->app->singleton('html.builder', function ($app)
		{
			return new Builder();
		});
	}
}
