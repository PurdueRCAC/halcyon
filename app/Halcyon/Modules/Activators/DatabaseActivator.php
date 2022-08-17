<?php

namespace App\Halcyon\Modules\Activators;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Schema;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Module;

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
	 * Array of modules activation statuses
	 *
	 * @var array
	 */
	private $modulesStatuses;

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
		$this->modulesStatuses = $this->getModulesStatuses();
	}

	/**
	 * @inheritDoc
	 */
	public function reset(): void
	{
		$this->modulesStatuses = [];
		$this->flushCache();
	}

	/**
	 * @inheritDoc
	 */
	public function enable(Module $module): void
	{
		$this->setActiveByName($module->getLowerName(), true);
	}

	/**
	 * @inheritDoc
	 */
	public function disable(Module $module): void
	{
		$this->setActiveByName($module->getLowerName(), false);
	}

	/**
	 * @inheritDoc
	 */
	public function hasStatus(Module $module, bool $status): bool
	{
		if (!isset($this->modulesStatuses[$module->getLowerName()]))
		{
			return $status === false;
		}

		return $this->modulesStatuses[$module->getLowerName()] === $status;
	}

	/**
	 * @inheritDoc
	 */
	public function setActive(Module $module, bool $active): void
	{
		$this->setActiveByName($module->getLowerName(), $active);
	}

	/**
	 * @inheritDoc
	 */
	public function setActiveByName(string $name, bool $status): void
	{
		$this->modulesStatuses[strtolower($name)] = $status;
		$this->writeDatabase();
		$this->flushCache();
	}

	/**
	 * @inheritDoc
	 */
	public function delete(Module $module): void
	{
		if (!isset($this->modulesStatuses[$module->getLowerName()]))
		{
			return;
		}
		unset($this->modulesStatuses[$module->getLowerName()]);
		$this->writeDatabase();
		$this->flushCache();
	}

	/**
	 * Writes the activation statuses to the database
	 */
	private function writeDatabase(): void
	{
		foreach ($this->modulesStatuses as $element => $enabled)
		{
			$this->db->table('extensions')
				->update(['enabled' => $enabled])
				->where('element', $element);
		}
	}

	/**
	 * Reads the database table that contains the activation statuses.
	 *
	 * @return  array
	 */
	private function readDatabase(): array
	{
		$modules = [];

		if (!Schema::hasTable('extensions'))
		{
			$modules['core'] = true;

			/*foreach (app('files')->directories(app_path('Modules')) as $dir)
			{
				$modules[strtolower(basename($dir))] = true;
			}*/

			return $modules;
		}

		$rows = $this->db->table('extensions')
			->select(['element', 'enabled', 'params'])
			->where('type', '=', 'module')
			->orderBy('ordering', 'asc')
			->get();

		foreach ($rows as $row)
		{
			if (trim($row->params))
			{
				$params = json_decode($row->params, true);

				$config = config()->get('module.' . strtolower($row->element), []);
				$config = array_merge($config, $params);

				config()->set('module.' . strtolower($row->element), $config);
			}

			$modules[strtolower($row->element)] = (bool) $row->enabled;
		}

		return $modules;
	}

	/**
	 * Get modules statuses, either from the cache or from
	 * the database if the cache is disabled.
	 *
	 * @return  array
	 */
	private function getModulesStatuses(): array
	{
		if (!$this->config->get('modules.cache.enabled'))
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
		return $this->config->get('modules.activators.database.' . $key, $default);
	}

	/**
	 * Flushes the modules activation statuses cache
	 */
	private function flushCache(): void
	{
		$this->cache->forget($this->cacheKey);
	}
}
