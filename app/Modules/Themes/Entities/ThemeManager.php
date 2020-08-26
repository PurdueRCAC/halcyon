<?php

namespace App\Modules\Themes\Entities;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use App\Modules\Themes\Models\Theme as Model;

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

		$directories = $this->getThemes();

		foreach ($directories as $theme)
		{
			$name = Str::studly($theme->name);

			if (!$this->getFinder()->isDirectory($this->path . '/' . $name))
			{
				continue;
			}

			$themes[$name] = new Theme($name, $this->path . '/' . $name, $theme->params);
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

		$directories = $this->getThemes();

		foreach ($directories as $theme)
		{
			$name = $theme->template;

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
	 * @return array
	 */
	private function getThemes()
	{
		//return $this->getFinder()->directories($this->path);
		$s = (new Model)->getTable();
		//$e = 'extensions';

		$query = $this->getDatabase()
			->table($s)
			->select([
				$s . '.id AS id',
				$s . '.enabled AS home',
				$s . '.name',
				$s . '.params',
				$s . '.protected'
			])
			//->join($e, $e . '.element', $s . '.template')
			//->where($s . '.client_id', '=', (int)$client_id)
			->where($s . '.enabled', '=', 1)
			->where($s . '.type', '=', 'theme')
			//->where($e . '.client_id', '=', $s . '.client_id')
			->orderBy($s . '.enabled', 'desc');

		return $query->get();
	}

	/**
	 * Get the theme directories
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
			$theme = $this->find($theme);

			if (!$theme)
			{
				throw new ThemeNotFoundException('Theme "' . $theme . '" not found.');
			}
		}

		$this->activeTheme = $theme;

		$this->activateViewPaths($theme);

		$this->activateLangPaths($theme);
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
		$type = $type == 'site' ? 1 : 0;

		return ($client_id == $type);
	}
}
