<?php
namespace App\Widgets\Adminmenu;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use App\Modules\Widgets\Entities\Widget;
use App\Modules\Menus\Models\Item;
use App\Modules\Menus\Models\Type;

/**
 * Widget class for displaying the admin menu
 */
class Adminmenu extends Widget
{
	/**
	 * Display module contents
	 *
	 * @return  null|\Illuminate\View\View
	 */
	public function run()
	{
		// Initialise variables.
		$menu    = new Tree();
		$enabled = true; //Request::input('hidemainmenu') ? false : true;

		if ($this->params->get('cache_data'))
		{
			$modules = Cache::remember($this->getCacheKey() . '.modules', $this->params->get('cache_data_time', 3600), function()
			{
				return $this->getModules(false);
			});

			$menus = Cache::remember($this->getCacheKey() . '.menus', $this->params->get('cache_data_time', 3600), function()
			{
				return $this->getMenus();
			});
		}
		else
		{
			$modules = $this->getModules(false);
			$menus = $this->getMenus();
		}

		$user = auth()->user();

		$groupings = array();
		foreach ($this->params->get('groupings', []) as $grouping)
		{
			$groupings[$grouping['grouping']] = $grouping['class'];
		}

		$mods = array(
			'dashboard' => array(),
			'system' => array(),
			'users' => array(),
			'content' => array(),
			'menus' => array(),
			'extensions' => array(),
			'themes' => array(),
		);
		foreach ($modules as $module)
		{
			if (!$user->can('manage ' . $module->element))
			{
				continue;
			}

			if (!$module->folder)
			{
				switch ($module->element)
				{
					case 'users':
					case 'menus':
					case 'dashboard':
					case 'themes':
						$module->folder = $module->element;
					break;
					case 'pages':
					case 'media':
					case 'tags':
						$module->folder = 'content';
					break;
					case 'core':
					case 'config':
					case 'history':
						$module->folder = 'system';
					break;
					default:
						$module->folder = 'extensions';
					break;
				}
			}

			if (!isset($mods[$module->folder]))
			{
				$mods[$module->folder] = array();
			}
			$mods[$module->folder][] = $module;
		}

		// Render the module layout
		return view($this->getViewName($enabled ? 'enabled' : 'disabled'), [
			'enabled'   => $enabled,
			'menu'      => $menu,
			'modules'   => $mods,
			'menus'     => $menus,
			'params'    => $this->params,
			'groupings' => $groupings,
		]);
	}

	/**
	 * Get a list of the available menus.
	 *
	 * @return  Collection
	 */
	public function getMenus(): Collection
	{
		$menus = (new Type)->getTable();
		$items = (new Item)->getTable();

		return DB::table($menus)
			->select(
				$menus . '.*',
				DB::raw('SUM(' . $items . '.home) AS home')
			)
			->leftJoin($items, $items . '.menutype', '=', $menus . '.menutype')
			->whereNull($menus . '.deleted_at')
			->where(function($where) use ($items)
			{
				$where->where($items . '.home', '!=', 0)
					->orWhere(function($query) use ($items)
					{
						$query->where($items . '.client_id', '=', 0)
							->orWhereNull($items . '.client_id');
					});
			})
			->groupBy($menus . '.id')
			->groupBy($menus . '.menutype')
			->groupBy($menus . '.description')
			->groupBy($menus . '.title')
			->groupBy($menus . '.client_id')
			->groupBy($menus . '.created_at')
			->groupBy($menus . '.updated_at')
			->groupBy($menus . '.deleted_at')
			->groupBy($items . '.menutype')
			->get();
	}

	/**
	 * Get a list of the authorised, non-special components to display in the components menu.
	 *
	 * @param   bool  $authCheck  An optional switch to turn off the auth check (to support custom layouts 'grey out' behaviour).
	 * @return  Collection  A collection of objects and submenus
	 */
	public function getModules(bool $authCheck = true): Collection
	{
		$items = (new Item)->getTable();
		$ext = 'extensions';

		// Prepare the query.
		$modules = DB::table($ext)
			->select(
				$ext . '.id',
				$ext . '.name',
				$ext . '.name AS title',
				$ext . '.element AS alias',
				$ext . '.element AS class',
				$ext . '.element',
				$ext . '.folder',
				$ext . '.protected'
			)
			->where($ext . '.client_id', '=', '1')
			->where($ext . '.enabled', '=', '1')
			->where($ext . '.type', '=', 'module')
			->orderBy($ext . '.ordering', 'asc')
			->orderBy($ext . '.name', 'asc')
			->get();

		// Initialise variables.
		$lang = app('translator');

		if (!$authCheck)
		{
			$modules->each(function ($module, $key) use ($lang)
			{
				if (!isset($module->submenu))
				{
					$module->submenu = array();
				}

				try
				{
					$module->link = route('admin.' . strtolower($module->element) . '.index');
				}
				catch (\Exception $e)
				{
					$module->link = url(config('app.admin-prefix', 'admin') . '/' . strtolower($module->element));
				}

				if (!empty($module->element))
				{
					$lang->addNamespace($module->element, app_path() . '/Modules/' . ucfirst($module->element) . '/Resources/lang');
				}

				$key = $module->element . '::system.' . $module->title;

				$module->text = $lang->has($key) ? trans($key) : $module->alias;
			});
		}

		return $modules;
	}
}
