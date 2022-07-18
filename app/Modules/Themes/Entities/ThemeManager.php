<?php

namespace App\Modules\Themes\Entities;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use App\Modules\Themes\Models\Theme as Model;
use Illuminate\Support\Facades\Schema;

class ThemeManager implements \Countable
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
	 * @var string Path to scan for themes
	 */
	private $activeTheme;

	/**
	 * Constructor
	 *
	 * @param Application $app
	 * @param $path
	 */
	public function __construct(Application $app, $path)
	{
		$this->app  = $app;
		$this->path = $path;
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
	 * Get path
	 *
	 * @return  string
	 */
	public function themePath($path): string
	{
		if ($theme = $this->getActiveTheme())
		{
			return $theme->getPath() . ($path ? '/' . trim($path, '/') : '');
		}
		return '';
	}

	/**
	 * Find a theme by name
	 *
	 * @param  string     $name
	 * @return Theme|null
	 */
	public function find($name)
	{
		foreach ($this->allEnabled() as $theme)
		{
			if ($theme->getLowerElement() == strtolower($name))
			{
				return $theme;
			}
		}

		return;
	}

	/**
	 * Get only the public themes
	 *
	 * @param  string $type
	 * @return array
	 */
	public function findEnabledByType($type = 'site')
	{
		foreach ($this->allByType($type) as $theme)
		{
			return $theme;
		}
	}

	/**
	 * Return all available themes
	 *
	 * @param  integer $state
	 * @return array
	 */
	public function all($state = null)
	{
		$themes = [];

		if (!$this->getFinder()->isDirectory($this->path))
		{
			return $themes;
		}

		$directories = $this->getThemes($state);

		foreach ($directories as $theme)
		{
			$name = Str::studly($theme->element);

			if (!$this->getFinder()->isDirectory($this->path . '/' . $name))
			{
				continue;
			}

			$themes[$name] = new Theme($name, $this->path . '/' . $name, $theme->params);
		}

		return $themes;
	}

	/**
	 * Return all enabled themes
	 *
	 * @return array
	 */
	public function allEnabled()
	{
		return $this->all(1);
	}

	/**
	 * Return all disabled themes
	 *
	 * @return array
	 */
	public function allDisabled()
	{
		return $this->all(0);
	}

	/**
	 * Get only the front-end themes
	 *
	 * @param  string $type
	 * @return array
	 */
	public function allByType($type = 'site')
	{
		$themes = [];

		if (!$this->getFinder()->isDirectory($this->path))
		{
			return $themes;
		}

		$directories = $this->getThemes(1);

		foreach ($directories as $theme)
		{
			$name = Str::studly($theme->element);

			if (!$this->getFinder()->isDirectory($this->path . '/' . $name))
			{
				continue;
			}

			if ($this->isType($theme->client_id, $type))
			{
				$themes[$name] = new Theme($name, $this->path . '/' . $name, $theme->params);
			}
		}

		return $themes;
	}

	/**
	 * Get the theme directories
	 *
	 * @param  integer $state
	 * @return array
	 */
	private function getThemes($state = null)
	{
		$s = (new Model)->getTable();

		if (!Schema::hasTable($s))
		{
			return $this->getThemesFromFiles($state);
		}

		return $this->getThemesFromDatabase($state);
	}

	/**
	 * Get the themes from filesystem directories
	 * 
	 * @param  integer  $state
	 * @return array
	 */
	private function getThemesFromFiles($state = null)
	{
		$dirs = $this->getFinder()->directories($this->path);

		$rows = array();
		foreach ($dirs as $dir)
		{
			$theme = new Model;
			$theme->name = basename($dir);
			$theme->element = strtolower($theme->name);
			$theme->client_id = 0;

			$rows[] = $theme;
		}

		return collect($rows);
	}

	/**
	 * Get the themes from the database
	 *
	 * @param  integer $state
	 * @param  string  $type
	 * @return array
	 */
	private function getThemesFromDatabase($state = null, $type = null)
	{
		$s = (new Model)->getTable();

		$query = $this->getDatabase()
			->table($s)
			->select([
				$s . '.id AS id',
				$s . '.enabled AS home',
				$s . '.name',
				$s . '.element',
				$s . '.params',
				$s . '.protected',
				$s . '.client_id'
			]);

		if (!is_null($state))
		{
			$query->where($s . '.enabled', '=', $state);
		}
		if (!is_null($type))
		{
			$query->where($s . '.client_id', '=', $type == 'admin' ? 1 : 0);
		}
		$query
			->where($s . '.type', '=', 'theme')
			->orderBy($s . '.enabled', 'desc');

		$rows = $query->get();

		if (count($rows) <= 0)
		{
			$rows = $this->getThemesFromFiles($state);
		}

		return $rows;
	}

	/**
	 * Get the theme directories
	 *
	 * @param  object $theme
	 * @return array
	 */
	private function registerTheme(Theme $theme)
	{
		$row = new Model;
		$row->fill([
			'type'      => 'theme',
			'name'      => $theme->getStudlyName(),
			'element'   => $theme->getLowerName(),
			'enabled'   => 1,
			'access'    => 1,
			'protected' => 0,
			'client_id' => $theme->getClient() == 'site' ? 1 : 0,
			'params'    => $theme->getParams()->toString()
		]);

		return $row->save();
	}

	/**
	 * Return the theme assets path
	 *
	 * @param  string $theme
	 * @return string
	 */
	public function getAssetPath($theme)
	{
		return public_path($this->getConfig()->get('module.themes.path.assets', 'themes') . '/' . $theme);
	}

	/**
	 * @return \Illuminate\Filesystem\Filesystem
	 */
	protected function getFinder()
	{
		return $this->app['files'];
	}

	/**
	 * @return \Illuminate\Filesystem\Filesystem
	 */
	protected function getDatabase()
	{
		return $this->app['db'];
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
			$found = $this->find($theme);

			if (!$found)
			{
				throw new ThemeNotFoundException('Theme "' . $theme . '" not found.');
			}

			$theme = $found;
		}

		$this->activeTheme = $theme;

		$this->activateViewPaths($this->activeTheme);

		$this->activateLangPaths($this->activeTheme);
	}

	/**
	 * Activates the view finder paths for a theme and its parents.
	 *
	 * @param Theme $theme
	 */
	protected function activateLangPaths(Theme $theme)
	{
		$this->app->make('translator')->addNamespace('theme', $theme->getPath() . '/lang');
	}

	/**
	 * Activates the view finder paths for a theme and its parents.
	 *
	 * @param Theme $theme
	 */
	protected function activateViewPaths(Theme $theme)
	{
		$this->app->get('view')->addLocation($theme->getPath() . '/views/');
	}

	/**
	 * Returns the theme json file
	 *
	 * @param $theme
	 * @return string
	 * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
	 */
	/*private function getThemeJsonFile($theme)
	{
		return json_decode($this->getFinder()->get("$theme/theme.json"));
	}*/

	/**
	 * @param  integer  $client_id
	 * @param  string   $type
	 * @return bool
	 */
	private function isType($client_id, $type = 'site')
	{
		$type = $type == 'site' ? 0 : 1;

		return ($client_id == $type);
	}
}
