<?php

namespace App\Providers;

//use App\Modules\Themes\Entities\ThemeManager;
use App\Halcyon\Html\Builder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Http\Request;
use Adldap\Adldap;
use Illuminate\Http\Resources\Json\JsonResource;

class HalcyonServiceProvider extends ServiceProvider
{
	/**
	 * Register services
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('isAdmin', function ()
		{
			return $this->isAdmin();
		});

		$this->app->singleton('html.builder', function ($app)
		{
			return new Builder();
		});

		$this->app->singleton('ldap', function ($app)
		{
			$config = (array)$app['config']->get('ldap', []);

			return new Adldap();
		});
	}

	/**
	 * Boot the package, in this case also discovering any themes required by stylist.
	 *
	 * @return void
	 */
	public function boot()
	{
		JsonResource::withoutWrapping();

		Blade::directive('sliders', function ($expression) {
			return "<?php echo app('html.builder')->sliders($expression); ?>";
		});
	}

	/**
	 * Publish the assets
	 *
	 * @param string $sourcePath
	 * @param string $destinationPath
	 * @return void
	 */
	public function publish($sourcePath, $destinationPath)
	{
		if (!$this->app['files']->isDirectory($sourcePath))
		{
			throw new \InvalidArgumentException("Source path does not exist : {$sourcePath}");
		}

		if (!$this->app['files']->isDirectory($destinationPath))
		{
			$this->app['files']->makeDirectory($destinationPath, 0775, true);
		}

		foreach ($this->app['files']->allFiles($sourcePath) as $file)
		{
			$dest = str_replace($sourcePath, $destinationPath, $file);

			if (!$this->app['files']->exists($dest)
			 || $this->app['files']->lastModified($file) > $this->app['files']->lastModified($dest))
			{
				if (!$this->app['files']->exists(dirname($dest)))
				{
					$this->app['files']->makeDirectory(dirname($dest), 0775, true);
				}

				$this->app['files']->copy($file, $dest);
			}
		}
	}

	/**
	 * Checks if the current url matches the configured backend uri
	 *
	 * @return bool
	 */
	private function isAdmin()
	{
		$url = app(Request::class)->segment(1);

		if ($url == $this->app['config']->get('app.admin-prefix', 'admin'))
		{
			return true;
		}

		return false;
	}
}
