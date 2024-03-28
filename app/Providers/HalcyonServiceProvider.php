<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Halcyon\Html\Builder;
use Adldap\Adldap;

class HalcyonServiceProvider extends ServiceProvider
{
	/**
	 * Register services
	 *
	 * @return void
	 */
	public function register(): void
	{
		$this->app->singleton('isAdmin', function ()
		{
			return $this->isAdmin();
		});

		$this->app->singleton('html.builder', function ($app)
		{
			return new Builder();
		});
	}

	/**
	 * Boot the package, in this case also discovering any themes required by stylist.
	 *
	 * @return void
	 */
	public function boot(): void
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
	public function publish(string $sourcePath, string $destinationPath): void
	{
		if (!$this->app->has('files'))
		{
			return;
		}

		$fs = $this->app['files'];

		if (!$fs->isDirectory($sourcePath))
		{
			throw new \InvalidArgumentException("Source path does not exist : {$sourcePath}");
		}

		if (!$fs->isDirectory($destinationPath))
		{
			$fs->makeDirectory($destinationPath, 0775, true);
		}

		foreach ($fs->allFiles($sourcePath) as $file)
		{
			$dest = str_replace($sourcePath, $destinationPath, $file);

			if (!$fs->exists($dest)
			 || $fs->lastModified($file) > $fs->lastModified($dest))
			{
				if (!$fs->exists(dirname($dest)))
				{
					$fs->makeDirectory(dirname($dest), 0775, true);
				}

				$fs->copy($file, $dest);
			}
		}
	}

	/**
	 * Checks if the current url matches the configured backend uri
	 *
	 * @return bool
	 */
	private function isAdmin(): bool
	{
		$url = app(Request::class)->segment(1);

		if ($url == config('app.admin-prefix', 'admin'))
		{
			return true;
		}

		return false;
	}
}
