<?php

namespace App\Modules\Themes\Entities;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use App\Modules\Themes\Models\Theme as Model;
use App\Modules\Themes\Contracts\RepositoryInterface;

class ThemeManager implements RepositoryInterface, \Countable
{
	/**
	 * @var Application
	 */
	private $app;

	/**
	 * Path to scan for themes
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Current active theme
	 *
	 * @var string
	 */
	private $activeTheme;

	/**
	 * Constructor
	 *
	 * @param Application $app
	 * @param string $path
	 * @return void
	 */
	public function __construct(Application $app, string $path)
	{
		$this->app  = $app;
		$this->path = $path;
	}

	/**
	 * Scan & get all available themes.
	 *
	 * @return array<int,string>
	 */
	public function scan(): array
	{
		return $this->getFiles()->directories($this->getScanPath());
	}

	/**
	 * Get the base path for themes
	 *
	 * @return  string
	 */
	public function getScanPath(): string
	{
		return $this->path;
	}

	/**
	 * Find a theme by name
	 *
	 * @param  string $name
	 * @return Theme|null
	 */
	public function find(string $name): ?Theme
	{
		$lname = strtolower($name);

		foreach ($this->allEnabled() as $theme)
		{
			if ($theme->getLowerElement() == $lname)
			{
				return $theme;
			}
		}

		foreach ($this->allDisabled() as $theme)
		{
			if ($theme->getLowerElement() == $lname)
			{
				return $theme;
			}
		}

		return null;
	}

	/**
	 * Find a specific theme. If there return that, otherwise throw exception.
	 *
	 * @param string $name
	 * @return Theme|null
	 * @throws ThemeNotFoundException
	 */
	public function findOrFail(string $name): ?Theme
	{
		$lname = strtolower($name);

		foreach ($this->allEnabled() as $theme)
		{
			if ($theme->getLowerElement() == $lname)
			{
				return $theme;
			}
		}

		foreach ($this->allDisabled() as $theme)
		{
			echo $theme->getLowerElement() . "\n";
			if ($theme->getLowerElement() == $lname)
			{
				return $theme;
			}
		}

		throw new ThemeNotFoundException("Theme [{$name}] does not exist!");
	}

	/**
	 * Get only enabled themes of a specific stype
	 *
	 * @param  string $type
	 * @return Theme|null
	 */
	public function findEnabledByType(string $type = 'site'): ?Theme
	{
		foreach ($this->allByType($type) as $theme)
		{
			return $theme;
		}

		return null;
	}

	/**
	 * Return all available themes
	 *
	 * @param  int $state
	 * @return array<string,Theme>
	 */
	public function all(int $state = null): array
	{
		$themes = [];

		if (!$this->getFiles()->isDirectory($this->path))
		{
			return $themes;
		}

		$directories = $this->getThemes($state);

		foreach ($directories as $theme)
		{
			$name = Str::studly($theme->element);

			if (!$this->getFiles()->isDirectory($this->path . '/' . $name))
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
	 * @return array<string,Theme>
	 */
	public function allEnabled(): array
	{
		return $this->all(1);
	}

	/**
	 * Return all disabled themes
	 *
	 * @return array<string,Theme>
	 */
	public function allDisabled(): array
	{
		return $this->all(0);
	}

	/**
	 * Get only the front-end themes
	 *
	 * @param  string $type
	 * @return array<string,Theme>
	 */
	public function allByType(string $type = 'site')
	{
		$themes = [];

		if (!$this->getFiles()->isDirectory($this->path))
		{
			return $themes;
		}

		$directories = $this->getThemes(1);

		foreach ($directories as $theme)
		{
			$name = Str::studly($theme->element);

			if (!$this->getFiles()->isDirectory($this->path . '/' . $name))
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
	 * Get themes as themes collection instance
	 *
	 * @return Collection
	 */
	public function toCollection(): Collection
	{
		return collect($this->all());
	}

	/**
	 * Get the theme directories
	 *
	 * @param  int $state
	 * @return Collection
	 */
	private function getThemes(int $state = null): Collection
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
	 * @param  int $state
	 * @return Collection
	 */
	private function getThemesFromFiles(int $state = null): Collection
	{
		$rows = array();

		foreach ($this->scan() as $dir)
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
	 * @param  int $state
	 * @param  string $type
	 * @return Collection
	 */
	private function getThemesFromDatabase(int $state = null, string $type = null): Collection
	{
		/*$s = (new Model)->getTable();

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
			]);*/

		$query = Model::query()
			->select([
				'id',
				'enabled',
				'name',
				'element',
				'params',
				'protected',
				'client_id'
			]);

		if (!is_null($state))
		{
			$query->where('enabled', '=', $state);
		}
		if (!is_null($type))
		{
			$query->where('client_id', '=', $type == 'admin' ? 1 : 0);
		}

		$rows = $query
			->where('type', '=', 'theme')
			->orderBy('enabled', 'desc')
			->get();

		if (count($rows) <= 0)
		{
			$rows = $this->getThemesFromFiles($state);
		}

		return $rows;
	}

	/**
	 * Get the theme directories
	 *
	 * @param  Theme $theme
	 * @return bool
	 */
	public function registerTheme(Theme $theme): bool
	{
		$row = new Model;
		$row->fill([
			'type'      => 'theme',
			'name'      => $theme->getStudlyName(),
			'element'   => $theme->getLowerName(),
			'enabled'   => 1,
			'access'    => 1,
			'protected' => 0,
			'client_id' => $theme->getType() == 'site' ? 0 : 1,
			'params'    => json_encode($theme->getParams()->all())
		]);

		return $row->save();
	}

	/**
	 * Return the theme assets path
	 *
	 * @param  string $theme
	 * @return string
	 */
	public function getAssetPath(string $theme): string
	{
		return public_path($this->getConfig()->get('module.themes.path.assets', 'themes') . '/' . $theme);
	}

	/**
	 * @return \Illuminate\Filesystem\Filesystem
	 */
	public function getFiles()
	{
		return $this->app['files'];
	}

	/**
	 * @return \Illuminate\Database\DatabaseManager
	 */
	/*protected function getDatabase()
	{
		return $this->app['db'];
	}*/

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
	public function count(): int
	{
		return count($this->all());
	}

	/**
	 * Activate a theme. Activation can be done by the theme's name, or via a Theme object.
	 *
	 * @return Theme|null
	 */
	public function getActiveTheme(): ?Theme
	{
		return $this->activeTheme;
	}

	/**
	 * Get path to the active theme
	 *
	 * @param  string $path
	 * @return string
	 */
	public function getActiveThemePath(string $path = null): string
	{
		if ($theme = $this->getActiveTheme())
		{
			return $theme->getPath() . ($path ? '/' . trim($path, '/') : '');
		}
		return '';
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
	 * @return void
	 */
	protected function activateLangPaths(Theme $theme): void
	{
		$this->app->make('translator')->addNamespace('theme', $theme->getPath() . '/lang');
	}

	/**
	 * Activates the view finder paths for a theme and its parents.
	 *
	 * @param Theme $theme
	 * @return void
	 */
	protected function activateViewPaths(Theme $theme): void
	{
		$this->app->get('view')->addLocation($theme->getPath() . '/views/');
	}

	/**
	 * Returns the theme json file
	 *
	 * @param  string $theme
	 * @return mixed
	 * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
	 */
	/*private function getThemeJsonFile($theme)
	{
		return json_decode($this->getFiles()->get("$theme/theme.json"));
	}*/

	/**
	 * @param  int  $client_id
	 * @param  string   $type
	 * @return bool
	 */
	private function isType(int $client_id, string $type = 'site'): bool
	{
		$type = $type == 'site' ? 0 : 1;

		return ($client_id == $type);
	}

	/**
	 * Determine whether the given module is activated.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function isEnabled(string $name): bool
	{
		$theme = $this->find($name);

		return $theme ? $theme->enabled : false;
	}

	/**
	 * Determine whether the given module is not activated.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function isDisabled(string $name): bool
	{
		$name = strtolower($name);

		foreach ($this->allDisabled() as $theme)
		{
			if ($theme->name == $name)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Delete a specific theme.
	 *
	 * @param string $theme
	 * @return bool
	 */
	public function delete(string $theme): bool
	{
		$target = null;
		$theme = strtolower($theme);

		foreach ($this->allEnabled() as $t)
		{
			if ($t->getLowerElement() == $theme)
			{
				$target = $t;
				break;
			}
		}

		if (!$target)
		{
			foreach ($this->allDisabled() as $t)
			{
				if ($theme->getLowerElement() == $theme)
				{
					$target = $t;
					break;
				}
			}
		}

		if ($target)
		{
			$target->delete();
		}

		return true;
	}
}
