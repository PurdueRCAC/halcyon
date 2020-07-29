<?php

namespace App\listeners\Listeners\Activators;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use App\listeners\Listeners\Contracts\ActivatorInterface;
use App\listeners\Listeners\Models\Listener;

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
	 * Array of listeners activation statuses
	 *
	 * @var array
	 */
	private $listenersStatuses;

	/**
	 * File used to store activation statuses
	 *
	 * @var string
	 */
	private $statusesFile;

	/**
	 * Constructor
	 *
	 * @param  Container  $app
	 * @return  void
	 */
	public function __construct(Container $app)
	{
		$this->cache = $app['cache'];
		$this->files = $app['files'];
		$this->config = $app['config'];
		$this->statusesFile = $this->config('statuses-file');
		$this->cacheKey = $this->config('cache-key');
		$this->cacheLifetime = $this->config('cache-lifetime');
		$this->listenersStatuses = $this->getlistenersStatuses();
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
		$this->listenersStatuses = [];
		$this->flushCache();
	}

	/**
	 * @inheritDoc
	 */
	public function enable(Listener $listener): void
	{
		$this->setActiveByName($listener->getName(), true);
	}

	/**
	 * @inheritDoc
	 */
	public function disable(Listener $listener): void
	{
		$this->setActiveByName($listener->getName(), false);
	}

	/**
	 * @inheritDoc
	 */
	public function hasStatus(Listener $listener, bool $status): bool
	{
		if (!isset($this->listenersStatuses[$listener->getName()]))
		{
			return $status === false;
		}

		return $this->listenersStatuses[$listener->getName()] === $status;
	}

	/**
	 * @inheritDoc
	 */
	public function setActive(Listener $listener, bool $active): void
	{
		$this->setActiveByName($listener->getName(), $active);
	}

	/**
	 * @inheritDoc
	 */
	public function setActiveByName(string $name, bool $status): void
	{
		$this->listenersStatuses[$name] = $status;
		$this->writeJson();
		$this->flushCache();
	}

	/**
	 * @inheritDoc
	 */
	public function delete(Listener $listener): void
	{
		if (!isset($this->listenersStatuses[$listener->getName()]))
		{
			return;
		}

		unset($this->listenersStatuses[$listener->getName()]);

		$this->writeJson();
		$this->flushCache();
	}

	/**
	 * Writes the activation statuses in a file, as json
	 */
	private function writeJson(): void
	{
		$this->files->put($this->statusesFile, json_encode($this->listenersStatuses, JSON_PRETTY_PRINT));
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
	 * Get listeners statuses, either from the cache or from
	 * the json statuses file if the cache is disabled.
	 * @return array
	 * @throws FileNotFoundException
	 */
	private function getlistenersStatuses(): array
	{
		if (!$this->config->get('listeners.cache.enabled'))
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
		return $this->config->get('listeners.activators.file.' . $key, $default);
	}

	/**
	 * Flushes the listeners activation statuses cache
	 */
	private function flushCache(): void
	{
		$this->cache->forget($this->cacheKey);
	}
}
