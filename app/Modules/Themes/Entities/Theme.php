<?php

namespace App\Modules\Themes\Entities;

use Illuminate\Config\Repository;
use Illuminate\Support\Str;

class Theme
{
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
	 * @var Repository
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

		if (is_string($params))
		{
			$params = json_decode($params, true);
		}
		$params = is_array($params) ? $params : [];

		$this->params = new Repository($params);
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
		return $this->get('requires', []);
	}

	/**
	 * Get theme type
	 *
	 * @return string
	 */
	public function getType(): string
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
	 * @param  string $file
	 * @return Json
	 */
	public function json($file = null): Repository
	{
		if ($file === null)
		{
			$file = 'theme.json';
		}

		return Arr::get($this->themeJson, $file, function () use ($file)
		{
			return $this->themeJson[$file] = new Repository(json_decode(file_get_contents($this->getPath() . '/' . $file), true));
		});
	}

	/**
	 * Get a specific data from json file by given the key.
	 *
	 * @param  string $key
	 * @param  null   $default
	 * @return mixed
	 */
	public function get(string $key, $default = null)
	{
		return $this->json()->get($key, $default);
	}

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
