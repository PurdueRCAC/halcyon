<?php

namespace Nwidart\Modules\Activators;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use App\Modules\Themes\Contracts\ActivatorInterface;
use Nwidart\Modules\Module;

class FileActivator implements ActivatorInterface
{
	/**
	 * Laravel cache instance
	 *
	 * @var CacheManager
	 */
	private $cache;

	/**
	 * Laravel Filesystem instance
	 *
	 * @var Filesystem
	 */
	private $files;

	/**
	 * Laravel config instance
	 *
	 * @var Config
	 */
	private $config;

	/**
	 * @var string
	 */
	private $cacheKey;

	/**
	 * @var string
	 */
	private $cacheLifetime;

	/**
	 * Array of themes activation statuses
	 *
	 * @var array
	 */
	private $themesStatuses;

	/**
	 * File used to store activation statuses
	 *
	 * @var string
	 */
	private $statusesFile;

	/**
	 * Constructor
	 *
	 * @param   Container  $app
	 * @return  void
	 */
	public function __construct(Container $app)
	{
		$this->cache  = $app['cache'];
		$this->files  = $app['files'];
		$this->config = $app['config'];

		$this->statusesFile   = $this->config('statuses-file');
		$this->cacheKey       = $this->config('cache-key');
		$this->cacheLifetime  = $this->config('cache-lifetime');
		$this->themesStatuses = $this->getThemesStatuses();
	}

	/**
	 * Get the path of the file where statuses are stored
	 *
	 * @return string
	 */
	public function getStatusesFilePath(): string
	{
		return $this->statusesFile;
	}

	/**
	 * @inheritDoc
	 */
	public function reset(): void
	{
		if ($this->files->exists($this->statusesFile))
		{
			$this->files->delete($this->statusesFile);
		}
		$this->themesStatuses = [];
		$this->flushCache();
	}

	/**
	 * @inheritDoc
	 */
	public function enable(Theme $theme): void
	{
		$this->setActiveByName($theme->getName(), true);
	}

	/**
	 * @inheritDoc
	 */
	public function disable(Theme $theme): void
	{
		$this->setActiveByName($theme->getName(), false);
	}

	/**
	 * @inheritDoc
	 */
	public function hasStatus(Theme $theme, bool $status): bool
	{
		if (!isset($this->themesStatuses[$theme->getName()]))
		{
			return $status === false;
		}

		return $this->themesStatuses[$theme->getName()] === $status;
	}

	/**
	 * @inheritDoc
	 */
	public function setActive(Module $theme, bool $active): void
	{
		$this->setActiveByName($theme->getName(), $active);
	}

	/**
	 * @inheritDoc
	 */
	public function setActiveByName(string $name, bool $status): void
	{
		$this->themesStatuses[$name] = $status;
		$this->writeJson();
		$this->flushCache();
	}

	/**
	 * @inheritDoc
	 */
	public function delete(Module $theme): void
	{
		if (!isset($this->themesStatuses[$theme->getName()]))
		{
			return;
		}

		unset($this->themesStatuses[$theme->getName()]);

		$this->writeJson();
		$this->flushCache();
	}

	/**
	 * Writes the activation statuses in a file, as json
	 */
	private function writeJson(): void
	{
		$this->files->put($this->statusesFile, json_encode($this->themesStatuses, JSON_PRETTY_PRINT));
	}

	/**
	 * Reads the json file that contains the activation statuses.
	 * @return array
	 * @throws FileNotFoundException
	 */
	private function readJson(): array
	{
		if (!$this->files->exists($this->statusesFile))
		{
			return [];
		}

		return json_decode($this->files->get($this->statusesFile), true);
	}

	/**
	 * Get themes statuses, either from the cache or from
	 * the json statuses file if the cache is disabled.
	 * @return array
	 * @throws FileNotFoundException
	 */
	private function getThemesStatuses(): array
	{
		if (!$this->config->get('themes.cache.enabled'))
		{
			return $this->readJson();
		}

		return $this->cache->remember($this->cacheKey, $this->cacheLifetime, function ()
		{
			return $this->readJson();
		});
	}

	/**
	 * Reads a config parameter under the 'activators.file' key
	 *
	 * @param  string $key
	 * @param  $default
	 * @return mixed
	 */
	private function config(string $key, $default = null)
	{
		return $this->config->get('themes.activators.file.' . $key, $default);
	}

	/**
	 * Flushes the themes activation statuses cache
	 */
	private function flushCache(): void
	{
		$this->cache->forget($this->cacheKey);
	}
}
