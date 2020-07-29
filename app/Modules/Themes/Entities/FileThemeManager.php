<?php

namespace App\Modules\Themes\Entities;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;

class FileThemeManager implements \Countable
{
	/**
	 * @var Application
	 */
	private $app;

	/**
	 * @var string Path to scan for themes
	 */
	private $path;

	/**
	 * @var string Path to scan for themes
	 */
	private $view;

	/**
	 * @param Application $app
	 * @param $path
	 */
	public function __construct(Application $app, $path)
	{
		$this->app  = $app;
		$this->path = $path;
		//$this->view = $this->app->make('view');
	}

	/**
	 * @param  string     $name
	 * @return Theme|null
	 */
	public function find($name)
	{
		foreach ($this->all() as $theme)
		{
			if ($theme->getLowerName() == strtolower($name))
			{
				return $theme;
			}
		}

		return;
	}

	/**
	 * Return all available themes
	 * @return array
	 */
	public function all()
	{
		$themes = [];

		if (!$this->getFinder()->isDirectory($this->path))
		{
			return $themes;
		}

		$directories = $this->getDirectories();

		foreach ($directories as $theme)
		{
			if (Str::startsWith($name = basename($theme), '.'))
			{
				continue;
			}

			$themes[$name] = new Theme($name, $theme);
		}

		return $themes;
	}

	/**
	 * Get only the public themes
	 * @return array
	 */
	public function allByType($type = 'site')
	{
		$themes = [];

		if (!$this->getFinder()->isDirectory($this->path))
		{
			return $themes;
		}

		$directories = $this->getDirectories();

		foreach ($directories as $theme)
		{
			if (Str::startsWith($name = basename($theme), '.'))
			{
				continue;
			}

			$themeJson = $this->getThemeJsonFile($theme);

			if ($this->isType($themeJson, $type))
			{
				$themes[$name] = new Theme($name, $theme, $this->getConfig()->get('themes.' . $name, []));
			}
		}

		return $themes;
	}

	/**
	 * Get the theme directories
	 * @return array
	 */
	private function getDirectories()
	{
		return $this->getFinder()->directories($this->path);
	}

	/**
	 * Return the theme assets path
	 * @param  string $theme
	 * @return string
	 */
	public function getAssetPath($theme)
	{
		return public_path($this->getConfig()->get('app.themes_assets_path', 'themes') . '/' . $theme);
	}

	/**
	 * @return \Illuminate\Filesystem\Filesystem
	 */
	protected function getFinder()
	{
		return $this->app['files'];
	}

	/**
	 * @return \Illuminate\Config\Repository
	 */
	protected function getConfig()
	{
		return $this->app['config'];
	}

	/**
	 * Counts all themes
	 */
	public function count()
	{
		return count($this->all());
	}

	/**
	 * Activate a theme. Activation can be done by the theme's name, or via a Theme object.
	 *
	 * @param string|Theme $theme
	 * @throws ThemeNotFoundException
	 */
	public function getActiveTheme()
	{
		return $this->activeTheme;
	}

	/**
	 * Activate a theme. Activation can be done by the theme's name, or via a Theme object.
	 *
	 * @param string|Theme $theme
	 * @throws ThemeNotFoundException
	 */
	public function activate($theme)
	{
		if (!$theme instanceof Theme)
		{
			$theme = $this->find($theme);
		}

		$this->activeTheme = $theme;

		$this->activateFinderPaths($theme);

		$this->app->make('translator')->addNamespace('theme', $theme->getPath() . '/lang');
	}

	/**
	 * Activates the view finder paths for a theme and its parents.
	 *
	 * @param Theme $theme
	 */
	protected function activateFinderPaths(Theme $theme)
	{
		$this->app->get('view')->addLocation($theme->getPath() . '/views/');
	}

	/**
	 * Returns the theme json file
	 * @param $theme
	 * @return string
	 * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
	 */
	private function getThemeJsonFile($theme)
	{
		return json_decode($this->getFinder()->get("$theme/theme.json"));
	}

	/**
	 * @param $themeJson
	 * @return bool
	 */
	private function isType($themeJson, $type = 'site')
	{
		return isset($themeJson->type) && $themeJson->type !== $type ? false : true;
	}
}
