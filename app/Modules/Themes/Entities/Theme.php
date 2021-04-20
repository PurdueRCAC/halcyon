<?php

namespace App\Modules\Themes\Entities;

use App\Halcyon\Config\Registry;
//use Illuminate\Container\Container;
use Illuminate\Support\Str;

class Theme
{
	/**
	 * The laravel|lumen application instance.
	 *
	 * @var \Illuminate\Contracts\Foundation\Application|\Laravel\Lumen\Application
	 */
	//private $app;

	/**
	 * the theme name
	 *
	 * @var string
	 */
	private $name;

	/**
	 * the theme path
	 *
	 * @var  string
	 */
	private $path;

	/**
	 * The theme params
	 *
	 * @var  array
	 */
	private $params;

	/**
	 * @var ActivatorInterface
	 */
	private $activator;

	/**
	 * Constructor
	 *
	 * @param   string  $name
	 * @param   string  $path
	 * @param   array   $params
	 * @return  void
	 */
	public function __construct($name, $path, $params = array())
	{
		//$this->app = $app;
		$this->name = $name;
		$this->path = realpath($path);
		$this->params = new Registry($params);
	}

	/**
	 * Get name
	 *
	 * @return  string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Get element
	 *
	 * @return  string
	 */
	public function getElement(): string
	{
		return basename($this->getPath());
	}

	/**
	 * Get element
	 *
	 * @return  string
	 */
	public function getLowerElement(): string
	{
		return strtolower($this->getElement());
	}

	/**
	 * Get path
	 *
	 * @return  string
	 */
	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * Set path
	 *
	 * @param  string $path
	 * @return $this
	 */
	public function setPath($path): Theme
	{
		$this->path = $path;

		return $this;
	}

	/**
	 * Get name in lower case.
	 *
	 * @return  string
	 */
	public function getLowerName(): string
	{
		return strtolower($this->name);
	}

	/**
	 * Get name in studly case.
	 *
	 * @return string
	 */
	public function getStudlyName(): string
	{
		return Str::studly($this->name);
	}

	/**
	 * Get name in snake case.
	 *
	 * @return string
	 */
	public function getSnakeName(): string
	{
		return Str::snake($this->name);
	}

	/**
	 * Get description.
	 *
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->get('description');
	}

	/**
	 * Get theme requirements.
	 *
	 * @return array
	 */
	public function getRequires(): array
	{
		return $this->get('requires');
	}

	/**
	 * Get theme type
	 *
	 * @return array
	 */
	public function getType(): array
	{
		$type = $this->get('client', 'site');

		if (is_numeric($type))
		{
			$type = $type ? 'site' : 'admin';
		}

		return $type;
	}

	/**
	 * Get params
	 *
	 * @param   string  $param
	 * @param   mixed   $default
	 * @return  mixed
	 */
	public function getParams($param = null, $default = null)
	{
		if ($param)
		{
			return $this->params->get($param, $default);
		}
		return $this->params;
	}

	/**
	 * Get json contents from the cache, setting as needed.
	 *
	 * @param string $file
	 *
	 * @return Json
	 */
	public function json($file = null): Registry
	{
		if ($file === null)
		{
			$file = 'theme.json';
		}

		return Arr::get($this->themeJson, $file, function () use ($file)
		{
			return $this->themeJson[$file] = new Registry($this->getPath() . '/' . $file);
		});
	}

	/**
	 * Get a specific data from json file by given the key.
	 *
	 * @param string $key
	 * @param null $default
	 *
	 * @return mixed
	 */
	public function get(string $key, $default = null)
	{
		return $this->json()->get($key, $default);
	}

	/**
	 * Determine whether the current module activated.
	 *
	 * @return bool
	 */
	/*public function isEnabled(): bool
	{
		return $this->activator->hasStatus($this, true);
	}*/

	/**
	 *  Determine whether the current module not disabled.
	 *
	 * @return bool
	 */
	/*public function isDisabled(): bool
	{
		return !$this->isEnabled();
	}*/

	/**
	 * Set active state for current module.
	 *
	 * @param bool $active
	 *
	 * @return bool
	 */
	/*public function setActive(bool $active): bool
	{
		return $this->activator->setActive($this, $active);
	}*/

	/**
	 * Disable the current module.
	 */
	/*public function disable(): void
	{
		$this->fireEvent('disabling');

		$this->activator->disable($this);
		$this->flushCache();

		$this->fireEvent('disabled');
	}*/

	/**
	 * Enable the current module.
	 */
	/*public function enable(): void
	{
		$this->fireEvent('enabling');

		$this->activator->enable($this);
		$this->flushCache();

		$this->fireEvent('enabled');
	}*/

	/**
	 * Register the module event.
	 *
	 * @param string $event
	 */
	/*protected function fireEvent($event): void
	{
		$this->app['events']->dispatch(sprintf('themes.%s.' . $event, $this->getLowerName()), [$this]);
	}*/

	/**
	 * Handle call __toString.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getStudlyName();
	}
}
