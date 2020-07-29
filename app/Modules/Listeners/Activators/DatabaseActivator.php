<?php

namespace App\listeners\Listeners\Activators;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use App\listeners\Listeners\Contracts\ActivatorInterface;
use App\listeners\Listeners\Models\Listener;

class DatabaseActivator implements ActivatorInterface
{
	/**
	 * Laravel cache instance
	 *
	 * @var CacheManager
	 */
	private $cache;

	/**
	 * Laravel database instance
	 *
	 * @var Database
	 */
	private $db;

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
	 * Database table used to store activation statuses
	 *
	 * @var string
	 */
	public function __construct(Container $app)
	{
		$this->cache  = $app['cache'];
		$this->db     = $app['db'];
		$this->config = $app['config'];
		$this->cacheKey = $this->config('cache-key');
		$this->cacheLifetime = $this->config('cache-lifetime');
		$this->listenersStatuses = $this->getlistenersStatuses();
	}

	/**
	 * @inheritDoc
	 */
	public function reset(): void
	{
		$this->listenersStatuses = [];
		$this->flushCache();
	}

	/**
	 * @inheritDoc
	 */
	public function enable(Listener $listener): void
	{
		$this->setActiveByName($listener->getLowerName(), true);
	}

	/**
	 * @inheritDoc
	 */
	public function disable(Listener $listener): void
	{
		$this->setActiveByName($listener->getLowerName(), false);
	}

	/**
	 * @inheritDoc
	 */
	public function hasStatus(Listener $listener, bool $status): bool
	{
		if (!isset($this->listenersStatuses[$listener->getLowerName()]))
		{
			return $status === false;
		}

		return $this->listenersStatuses[$listener->getLowerName()] === $status;
	}

	/**
	 * @inheritDoc
	 */
	public function setActive(Listener $listener, bool $active): void
	{
		$this->setActiveByName($listener->getLowerName(), $active);
	}

	/**
	 * @inheritDoc
	 */
	public function setActiveByName(string $name, bool $status): void
	{
		$this->listenersStatuses[strtolower($name)] = $status;
		$this->writeDatabase();
		$this->flushCache();
	}

	/**
	 * @inheritDoc
	 */
	public function delete(Listener $listener): void
	{
		if (!isset($this->listenersStatuses[$listener->getLowerName()]))
		{
			return;
		}

		unset($this->listenersStatuses[$listener->getLowerName()]);

		$this->writeDatabase();
		$this->flushCache();
	}

	/**
	 * Writes the activation statuses to the database
	 */
	private function writeDatabase(): void
	{
		foreach ($this->listenersStatuses as $element => $enabled)
		{
			$this->db->getQuery()
				->table('extensions')
				->update(['enabled' => $enabled])
				->where('element', $element);
		}
	}

	/**
	 * Reads the json file that contains the activation statuses.
	 *
	 * @return  array
	 */
	private function readDatabase(): array
	{
		$listeners = [];

		$rows = $this->db->table('extensions')
			->select(['element', 'enabled'])
			->where('type', '=', 'listener')
			->get();

		foreach ($rows as $row)
		{
			$listeners[strtolower($row->element)] = (bool) $row->enabled;
		}

		return $listeners;
	}

	/**
	 * Get listeners statuses, either from the cache or from
	 * the json statuses file if the cache is disabled.
	 *
	 * @return  array
	 */
	private function getlistenersStatuses(): array
	{
		if (!$this->config->get('listeners.cache.enabled'))
		{
			return $this->readDatabase();
		}

		return $this->cache->remember($this->cacheKey, $this->cacheLifetime, function ()
		{
			return $this->readDatabase();
		});
	}

	/**
	 * Reads a config parameter under the 'activators.file' key
	 *
	 * @param   string  $key
	 * @param   mixed   $default
	 * @return  mixed
	 */
	private function config(string $key, $default = null)
	{
		return $this->config->get('listeners.activators.database.' . $key, $default);
	}

	/**
	 * Flushes the listeners activation statuses cache
	 */
	private function flushCache(): void
	{
		$this->cache->forget($this->cacheKey);
	}
}
