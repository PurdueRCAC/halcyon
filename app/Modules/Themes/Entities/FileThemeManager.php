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
	 * @var Theme|null
	 */
	private $activeTheme = null;

	/**
	 * @param Application $app
	 * @param string $path
	 * @return void
	 */
	public function __construct(Application $app, string $path)
	{
		$this->app  = $app;
		$this->path = $path;
		//$this->view = $this->app->make('view');
	}

	/**
	 * Find a theme by name
	 *
	 * @param  string  $name
	 * @return Theme|null
	 */
	public function find(string $name): ?Theme
	{
		$name = strtolower($name);

		foreach ($this->all() as $theme)
		{
			if ($theme->getLowerName() == $name)
			{
				return $theme;
			}
		}

		return null;
	}

	/**
	 * Return all available themes
	 *
	 * @return array<string,Theme>
	 */
	public function all(): array
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
	 *
	 * @return array<string,Theme>
	 */
	public function allByType(string $type = 'site'): array
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
	 *
	 * @return array
	 */
	private function getDirectories()
	{
		return $this->getFinder()->directories($this->path);
	}

	/**
	 * Return the theme assets path
	 *
	 * @param  string $theme
	 * @return string
	 */
	public function getAssetPath(string $theme): string
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
	 *
	 * @return int
	 */
	public function count(): int
	{
		return count($this->all());
	}

	/**
	 * Get the active theme
	 *
	 * @return Theme|null
	 */
	public function getActiveTheme(): ?Theme
	{
		return $this->activeTheme;
	}

	/**
	 * Activate a theme. Activation can be done by the theme's name, or via a Theme object.
	 *
	 * @param string|Theme $theme
	 * @return void
	 * @throws ThemeNotFoundException
	 */
	public function activate($theme): void
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
	 * @return void
	 */
	protected function activateFinderPaths(Theme $theme): void
	{
		$this->app->get('view')->addLocation($theme->getPath() . '/views/');
	}

	/**
	 * Returns the theme json file
	 *
	 * @param  string $theme
	 * @return \stdClass
	 * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
	 */
	private function getThemeJsonFile(string $theme)
	{
		return json_decode($this->getFinder()->get("$theme/theme.json"));
	}

	/**
	 * Check if a theme is a specific type (site|admin)
	 *
	 * @param \stdClass $themeJson
	 * @param string $type
	 * @return bool
	 */
	private function isType($themeJson, string $type = 'site'): bool
	{
		return isset($themeJson->type) && $themeJson->type !== $type ? false : true;
	}
}
