<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Html;

use Illuminate\Support\ServiceProvider;
use App\Halcyon\Html\Toolbar;

/**
 * Toolbar service provider
 */
class ToolbarServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return  void
	 */
	public function register()
	{
		if ($this->app['request']->segment(1) == $this->app['config']->get('app.admin-prefix', 'admin'))
		{
			$this->registerToolbar();

			//$this->registerSubmenu();
		}
	}

	/**
	 * Register the toolbar.
	 *
	 * @return  void
	 */
	public function registerToolbar()
	{
		$this->app->singleton('toolbar', function ($app)
		{
			return new Toolbar('toolbar');
		});
	}

	/**
	 * Register the submenu.
	 *
	 * @return  void
	 */
	/*public function registerSubmenu()
	{
		$this->app->singleton('submenu', function ($app)
		{
			return new Toolbar('submenu');
		});
	}*/

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	/*public function boot()
	{
		Blade::directive('toolbar', function ($expression)
		{
			$expression = ($expression[0] === '(') ? substr($expression, 1, -1) : $expression;

			return "<?php echo \App\Halcyon\Html\Toolbar::render([{$expression}]); ?>";
		});
	}*/
}
