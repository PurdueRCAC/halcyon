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

			return new Adldap();//$config);
		});

		/*if (class_exists(TranslationServiceProvider::class))
		{
			$this->app->register(TranslationServiceProvider::class);
		}

		$this->app->register(LaravelModulesServiceProvider::class);

		$loader = AliasLoader::getInstance();
		$loader->alias('Toolbar', Toolbar::class);*/
	}

	/**
	 * Boot the package, in this case also discovering any themes required by stylist.
	 */
	public function boot()
	{
		/*$manager = $this->app['themes'];

		//$themePaths = $manager->all();
		$client = $this->isAdmin() ? 'admin' : 'site';

		$theme = $this->app['config']->get('app.' . $client . '-theme', null);

		if (!is_null($theme))
		{
			$theme = $manager->find($theme);

			$manager->activate($theme);

			$this->publish($theme->getPath() . '/assets', $manager->getAssetPath($theme->getName()));

			$this->publishes([
				$theme->getPath() . '/assets' => $manager->getAssetPath($theme->getName()),
			], 'public');
		}*/
		JsonResource::withoutWrapping();

		Blade::directive('sliders', function ($expression) {
			return "<?php echo app('html.builder')->sliders($expression); ?>";
		});
	}

	/**
	 * Publish the assets
	 */
	public function publish($sourcePath, $destinationPath)
	{
		if (!$this->app['files']->isDirectory($sourcePath))
		{
			$message = "Source path does not exist : {$sourcePath}";
			throw new \InvalidArgumentException($message);
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
		/*if ($this->app['files']->copyDirectory($sourcePath, $destinationPath))
		{
			return true;
		}*/
	}

	/**
	 * Checks if the current url matches the configured backend uri
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
