<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Menus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Halcyon\Form\Form;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use App\Modules\Menus\Events\TypeCreating;
use App\Modules\Menus\Events\TypeCreated;
use App\Modules\Menus\Events\TypeUpdating;
use App\Modules\Menus\Events\TypeUpdated;
use App\Modules\Menus\Events\TypeDeleted;

/**
 * Model for news type
 */
class Type extends Model
{
	use ErrorBag, Validatable, Historable, SoftDeletes;

	/**
	 * The table to which the class pertains
	 *
	 * This will default to #__{namespace}_{modelName} unless otherwise
	 * overwritten by a given subclass. Definition of this property likely
	 * indicates some derivation from standard naming conventions.
	 *
	 * @var  string
	 **/
	protected $table = 'menus';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	//public $timestamps = false;

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'title';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'title'    => 'notempty',
		'menutype' => 'notempty'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'creating' => TypeCreating::class,
		'created'  => TypeCreated::class,
		'updating' => TypeUpdating::class,
		'updated'  => TypeUpdated::class,
		'deleted'  => TypeDeleted::class,
		//'restored' => TypeRestored::class,
	];

	/**
	 * Generates automatic owned by field value
	 *
	 * @param   array   $data  the data being saved
	 * @return  string
	 */
	public function automaticMenutype($data)
	{
		$alias = (isset($data['menutype']) && $data['menutype'] ? $data['menutype'] : $data['title']);
		$alias = str_replace(' ', '-', $alias);
		return preg_replace("/[^a-zA-Z0-9\-]/", '', strtolower($alias));
	}

	/**
	 * Runs extra setup code when creating a new model
	 *
	 * @return  void
	 */
	protected static function boot()
	{
		parent::boot();

		static::saving(function ($model)
		{
			$exist = self::query()
				->where('menutype', '=', $model->menutype)
				->where('id', '!=', $model->id)
				->first();

			if ($exist && $exist->id)
			{
				$model->addError(trans('An entry with the menutype ":menutype" already exists.', ['menutype' => $model->menutype]));
				return false;
			}

			return true;
		});
	}

	/**
	 * Get a list of menu items
	 *
	 * @return  object
	 */
	public function items()
	{
		return $this->hasMany(Item::class, 'menutype', 'menutype');
	}

	/**
	 * Get a menu's items as a tree
	 *
	 * @return  object
	 */
	public function getTreeAttribute()
	{
		$table = (new Item)->getTable();

		$query = DB::table($table . ' AS a')
			->select(['a.id AS value', 'a.title AS text', 'a.level'])
			->leftJoin($table . ' AS b', function($join)
				{
					$join->on('a.lft', '>', 'b.lft')
						->on('a.rgt', '<', 'b.rgt');
				});

		$query->where('a.menutype', '=', $this->menutype);

		$query->where('a.published', '!=', '-2')
			->groupBy('a.id', 'a.title', 'a.level', 'a.lft', 'a.rgt', 'a.menutype', 'a.parent_id', 'a.published')
			->orderBy('a.lft', 'asc');

		return $query->get();
	}

	/**
	 * Get a list of widget menu items
	 *
	 * @return  object
	 */
	public function widgets()
	{
		$query = Widget::query()
			->where('widget', '=', 'menu')
			->where('params', 'like', '%"menutype":' . json_encode($this->menutype) . '%');

		return $query;
	}

	/**
	 * Get a count of published menu items
	 *
	 * @return  integer
	 */
	public function countPublishedItems()
	{
		$total = $this->items()
			->where('published', '=', 1)
			->count();

		return (int)$total;
	}

	/**
	 * Get a count of unpublished menu items
	 *
	 * @return  integer
	 */
	public function countUnpublishedItems()
	{
		$total = $this->items()
			->where('published', '=', 0)
			->count();

		return (int)$total;
	}

	/**
	 * Get a count of unpublished menu items
	 *
	 * @return  integer
	 */
	public function countTrashedItems()
	{
		$total = $this->items()
			->onlyTrashed()
			->count();

		return (int)$total;
	}

	/**
	 * Get a form
	 *
	 * @return  object
	 */
	public function getForm()
	{
		$file = __DIR__ . '/Forms/menu.xml';
		//$file = Filesystem::cleanPath($file);

		Form::addFieldPath(__DIR__ . '/Fields');

		$form = new Form('menu', array('control' => 'fields'));

		if (!$form->loadFile($file, false, '//form'))
		{
			$this->addError(trans('global.error.failed to load file'));
		}

		$data = $this->toArray();
		$form->bind($data);

		return $form;
	}

	/**
	 * Method rebuild the entire nested set tree.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 */
	public function rebuild()
	{
		// Initialiase variables.
		$items = new Item;

		if (!$items->rebuild(1))
		{
			$this->addError($items->getError());
			return false;
		}

		return true;
	}

	/**
	 * Gets a list of all mod_mainmenu modules and collates them by menutype
	 *
	 * @return  array
	 */
	public static function getWidgets()
	{
		/*$m = Widget::blank()->getTable();

		$db = \App::get('db');

		$query = $db->getQuery();
		$query->from($m, 'a');
		$query->select('a.id');
		$query->select('a.title');
		$query->select('a.params');
		$query->select('a.position');
		$query->whereEquals('module', Module::MODULE_NAME);
		$query->select('ag.title', 'access_title');
		$query->join('#__viewlevels AS ag', 'ag.id', 'a.access', 'left');

		$db->setQuery($query->toString());

		$modules = $db->loadObjectList();*/

		$widgets = Widget::mainMenus();

		$result = array();

		foreach ($widgets as $widget)
		{
			$params = new \App\Halcyon\Config\Registry($widget->params);

			$menuType = $params->get('menutype');
			if (!isset($result[$menuType]))
			{
				$result[$menuType] = array();
			}
			$result[$menuType][] = $widgets;
		}

		return $result;
	}

	/**
	 * Find a model by menutype
	 *
	 * @param  mixed  $id
	 * @param  array  $columns
	 * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
	 */
	public static function findByMenutype($type, $columns = ['*'])
	{
		return static::query()->where('menutype', '=', $type)->first($columns);
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @return  boolean  False if error, True on success
	 */
	public function save(array $options = [])
	{
		if ($this->id)
		{
			// Get the old value of the table just in case the 'menutype' changed
			$prev = self::find($this->id);

			if ($this->menutype != $prev->menutype) //$prev->getOriginal('menutype'))
			{
				// Get the user id
				$userId = auth()->user()->id;

				// Verify that no items are checked out
				$checked_out = $prev->items()
					->where('checked_out', '!=', (int) $userId)
					->where('checked_out', '!=', 0)
					->count();

				if ($checked_out)
				{
					$this->addError(
						trans('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), trans('JLIB_DATABASE_ERROR_MENUTYPE_CHECKOUT'))
					);
					return false;
				}

				// Verify that no module for this menu are checked out
				$checked_out = $prev->widgets()
					->where('checked_out', '!=', (int) $userId)
					->where('checked_out', '!=', 0)
					->count();

				if ($checked_out)
				{
					$this->addError(
						trans('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), trans('JLIB_DATABASE_ERROR_MENUTYPE_CHECKOUT'))
					);
					return false;
				}

				DB::table((new Item)->getTable())
					->where('menutype', '=', $prev->menutype)
					->update(['menutype' => $this->menutype]);

				// Update the menu items
				/*foreach ($prev->items as $item)
				{
					$item->menutype = $this->menutype;

					if (!$item->save())
					{
						$this->addError(trans('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), $item->getError()));
						return false;
					}
				}*/

				// Update the module items
				foreach ($prev->widgets()->get() as $widget)
				{
					$widget->params->set('menutype', $this->menutype);
					$widget->params = $widget->params->toString();

					if (!$widget->save())
					{
						$this->addError(trans('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), $widget->getError()));
						return false;
					}
				}
			}
		}

		return parent::save($options);
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @return  boolean  False if error, True on success
	 */
	public function delete(array $options = [])
	{
		// Get the user id
		$userId = auth()->user()->id;

		// Verify that no items are checked out
		$checked_out = $this->items()
			->where('checked_out', '!=', (int) $userId)
			->where('checked_out', '!=', 0)
			->count();

		if ($checked_out)
		{
			$this->addError(
				trans('global.error.delete failed', get_class($this), trans('menus::menus.error.checked out'))
			);
			return false;
		}

		// Verify that no module for this menu are checked out
		$checked_out = $this->widgets()
			->where('checked_out', '!=', (int) $userId)
			->where('checked_out', '!=', 0)
			->count();

		if ($checked_out)
		{
			$this->addError(
				trans('global.error.delete failed', get_class($this), trans('menus::menus.error.checked out'))
			);
			return false;
		}

		// Delete the menu items
		foreach ($this->items as $item)
		{
			if (!$item->delete())
			{
				$this->addError($item->getError());
				return false;
			}
		}

		// Delete the module items
		foreach ($this->widgets()->get() as $module)
		{
			if (!$module->delete())
			{
				$this->addError($module->getError());
				return false;
			}
		}

		// Attempt to delete the record
		return parent::delete($options);
	}
}
