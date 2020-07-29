<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Halycon\Plugin;

use Halycon\Events\Dispatcher;
use Halycon\Events\DispatcherInterface;
use Halycon\Events\LoaderInterface;
use Halycon\Config\Registry;
use Exception;
use stdClass;

/**
 * Plugin loader
 */
class Loader implements LoaderInterface
{
	/**
	 * A persistent cache of the loaded plugins.
	 *
	 * @var  array
	 */
	protected static $plugins = null;

	/**
	 * Get the event name.
	 *
	 * @return  string  The event name.
	 */
	public function getName()
	{
		return 'plugins';
	}

	/**
	 * Get the plugin data of a specific type if no specific plugin is specified
	 * otherwise only the specific plugin data is returned.
	 *
	 * @param   string  $type    The plugin type, relates to the sub-directory in the plugins directory.
	 * @param   string  $plugin  The plugin name.
	 * @return  mixed   An array of plugin data objects, or a plugin data object.
	 */
	public function loadListeners($type)
	{
		$results = array();
		$plugins = (array) $this->byType($type);

		foreach ($plugins as $p)
		{
			if ($result = $this->init($p))
			{
				$results[] = $result;
			}
		}

		return $results;
	}

	/**
	 * Get the params for a specific plugin
	 *
	 * @param   string  $type    The plugin type, relates to the sub-directory in the plugins directory.
	 * @param   string  $plugin  The plugin name.
	 * @return  object
	 */
	public function params($type, $plugin)
	{
		$result = $this->byType($type, $plugin);

		if (!$result || empty($result))
		{
			$result = new stdClass;
			$result->params = '';
		}

		if (is_string($result->params))
		{
			$result->params = new Registry($result->params);
		}

		return $result->params;
	}

	/**
	 * Get the plugin data of a specific type if no specific plugin is specified
	 * otherwise only the specific plugin data is returned.
	 *
	 * @param   string  $type    The plugin type, relates to the sub-directory in the plugins directory.
	 * @param   string  $plugin  The plugin name.
	 * @return  mixed   An array of plugin data objects, or a plugin data object.
	 */
	public function byType($type, $plugin = null)
	{
		$result = array();

		foreach ($this->all() as $p)
		{
			// Is this the right plugin?
			if ($p->type == $type)
			{
				if ($plugin)
				{
					if ($p->name == $plugin)
					{
						$result = $p;
						break;
					}
				}
				else
				{
					$result[] = $p;
				}
			}
		}

		return $result;
	}

	/**
	 * Checks if a plugin is enabled.
	 *
	 * @param   string   $type    The plugin type, relates to the sub-directory in the plugins directory.
	 * @param   string   $plugin  The plugin name.
	 * @return  boolean
	 */
	public function isEnabled($type, $plugin = null)
	{
		$result = $this->byType($type, $plugin);

		return (!empty($result));
	}

	/**
	 * Get a plugin's path
	 *
	 * @param   string  $type    The plugin type, relates to the sub-directory in the plugins directory.
	 * @param   string  $plugin  The plugin name.
	 * @return  string
	 */
	public function path($type, $plugin = null)
	{
		static $paths = array();

		if (!isset($paths[$type . $plugin]))
		{
			$paths[$type . $plugin] = '';

			$p = DIRECTORY_SEPARATOR . 'Listeners' . DIRECTORY_SEPARATOR . $type . ($plugin ? DIRECTORY_SEPARATOR . $plugin : '');

			$base = app_path();

			if (is_dir($base . $p))
			{
				$paths[$type . $plugin] = $base . $p;
			}
		}

		return $paths[$type . $plugin];
	}

	/**
	 * Loads all the plugin files for a particular type if no specific plugin is specified
	 * otherwise only the specific plugin is loaded.
	 *
	 * @param   string   $type        The plugin type, relates to the sub-directory in the plugins directory.
	 * @param   string   $plugin      The plugin name.
	 * @param   boolean  $autocreate  Autocreate the plugin.
	 * @param   object   $dispatcher  Optionally allows the plugin to use a different dispatcher.
	 * @return  boolean  True on success.
	 */
	public function import($type, $plugin = null, $autocreate = true, $dispatcher = null)
	{
		static $loaded = array();

		// check for the default args, if so we can optimise cheaply
		$defaults = false;
		if (is_null($plugin) && $autocreate == true && is_null($dispatcher))
		{
			$defaults = true;
		}

		if (!isset($loaded[$type]) || !$defaults)
		{
			$results = null;

			// Makes sure we have an event dispatcher
			if (!($dispatcher instanceof DispatcherInterface))
			{
				$dispatcher = App::get('dispatcher');
			}

			// Get the specified plugin(s).
			foreach ($this->all() as $plug)
			{
				if ($plug->type == $type && ($plugin === null || $plug->name == $plugin))
				{
					if ($p = $this->init($plug, $autocreate)) //, $dispatcher))
					{
						$dispatcher->addListener($p);
					}
					$results = true;
				}
			}

			// Bail out early if we're not using default args
			if (!$defaults)
			{
				return $results;
			}
			$loaded[$type] = $results;
		}

		return $loaded[$type];
	}

	/**
	 * Loads the plugin file.
	 *
	 * @param   object   $plugin  The plugin data.
	 * @return  boolean  True on success.
	 */
	protected function init(&$plugin, $autocreate = true, $dispatcher = null)
	{
		$plugin->type = preg_replace('/[^A-Z0-9_\.-]/i', '', $plugin->type);
		$plugin->name = preg_replace('/[^A-Z0-9_\.-]/i', '', $plugin->name);

		$className = 'App\\Listeners\\' . ucfirst($plugin->type) . '\\' . ucfirst($plugin->name);

		// If the class exists, the file was already loaded
		if (!class_exists($classNameL) && !class_exists($classNameN))
		{
			$path = $this->path($plugin->type, $plugin->name) . DIRECTORY_SEPARATOR . $plugin->name . '.php';

			if (file_exists($path))
			{
				require_once $path;

				if ($autocreate)
				{
					if (!class_exists($className))
					{
						continue;
					}

					$params = config('plugins.' . $plugin->type . '.' . $plugin->name, '');

					if ($params)
					{
						$plugin->params = json_encode($params);
					}

					// Makes sure we have an event dispatcher
					if (!($dispatcher instanceof DispatcherInterface))
					{
						$dispatcher = new Dispatcher();
					}

					// Instantiate and register the plugin.
					return new $className($dispatcher, (array) $plugin);
				}
			}
		}

		return null;
	}

	/**
	 * Loads the published plugins.
	 *
	 * @return  array  An array of published plugins
	 */
	public function all()
	{
		if (self::$plugins !== null)
		{
			return self::$plugins;
		}

		if (!app()->has('cache.store') || !($cache = app('cache.store')))
		{
			$cache = new \Halcyon\Cache\Storage\None();
		}

		$levels = implode(',', User::getAuthorisedViewLevels());

		if (!(self::$plugins = $cache->get('com_plugins.' . $levels)))
		{
			$db = app('db');

			$query = $db->getQuery()
				->select('folder', 'type')
				->select('element', 'name')
				->select('protected')
				->select('params')
				->from('extensions')
				->where('enabled', '>=', 1)
				->whereEquals('type', 'plugin')
				->where('state', '>=', 0)
				->whereIn('access', $levels)
				->order('ordering', 'asc');

			self::$plugins = $db->setQuery($query->toString())->loadObjectList();

			$cache->put('com_plugins.' . $levels, self::$plugins, config('cache.time', 15));
		}

		return self::$plugins;
	}

	/**
	 * Loads the language file for a plugin
	 *
	 * @param   string   $extension  Plugin name
	 * @param   string   $basePath   Path to load from
	 * @return  boolean
	 */
	public function language($extension, $basePath = PATH_CORE)
	{
		return app('translation')->load(strtolower($extension), $basePath);
	}
}
