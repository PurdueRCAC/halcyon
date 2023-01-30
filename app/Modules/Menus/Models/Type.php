<?php

namespace App\Modules\Menus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Halcyon\Form\Form;
use App\Modules\History\Traits\Historable;
use App\Modules\Menus\Events\TypeCreating;
use App\Modules\Menus\Events\TypeCreated;
use App\Modules\Menus\Events\TypeUpdating;
use App\Modules\Menus\Events\TypeUpdated;
use App\Modules\Menus\Events\TypeDeleted;

/**
 * Model for news type
 *
 * @property int    $id
 * @property string $menutype
 * @property string $title
 * @property string $description
 * @property int    $client_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Type extends Model
{
	use Historable, SoftDeletes;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'menus';

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
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array<string,string>
	 */
	protected $rules = array(
		'title'    => 'required|string|max:255',
		'menutype' => 'required|string|max:255'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'creating' => TypeCreating::class,
		'created'  => TypeCreated::class,
		'updating' => TypeUpdating::class,
		'updated'  => TypeUpdated::class,
		'deleted'  => TypeDeleted::class,
	];

	/**
	 * Generates automatic owned by field value
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setMenutypeAttribute(string $value): void
	{
		$value = str_replace(' ', '-', $value);
		$value = preg_replace("/[^a-zA-Z0-9\-]/", '', strtolower($value));

		$this->attributes['menutype'] = $value;
	}

	/**
	 * Runs extra setup code when creating a new model
	 *
	 * @return  void
	 */
	protected static function boot(): void
	{
		parent::boot();

		static::creating(function ($model)
		{
			$exist = self::query()
				->where('menutype', '=', $model->menutype)
				->where('id', '!=', $model->id)
				->first();

			if ($exist && $exist->id)
			{
				throw new \Exception(
					trans('An entry with the menutype ":menutype" already exists.', ['menutype' => $model->menutype])
				);
			}

			return true;
		});
	}

	/**
	 * Get a list of menu items
	 *
	 * @return  HasMany
	 */
	public function items(): HasMany
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
	 * @return  int
	 */
	public function countPublishedItems(): int
	{
		$total = $this->items()
			->where('published', '=', 1)
			->count();

		return (int)$total;
	}

	/**
	 * Get a count of unpublished menu items
	 *
	 * @return  int
	 */
	public function countUnpublishedItems(): int
	{
		$total = $this->items()
			->where('published', '=', 0)
			->count();

		return (int)$total;
	}

	/**
	 * Get a count of unpublished menu items
	 *
	 * @return  int
	 */
	public function countTrashedItems(): int
	{
		$total = $this->items()
			->onlyTrashed()
			->count();

		return (int)$total;
	}

	/**
	 * Get a form
	 *
	 * @return  Form
	 */
	public function getForm(): Form
	{
		$file = __DIR__ . '/Forms/menu.xml';
		//$file = Filesystem::cleanPath($file);

		Form::addFieldPath(__DIR__ . '/Fields');

		$form = new Form('menu', array('control' => 'fields'));

		if (!$form->loadFile($file, false, '//form'))
		{
			throw new \Exception(trans('global.error.failed to load file'));
		}

		$data = $this->toArray();
		$form->bind($data);

		return $form;
	}

	/**
	 * Method rebuild the entire nested set tree.
	 *
	 * @return  bool  False on failure or error, true otherwise.
	 */
	public function rebuild()
	{
		// Initialiase variables.
		$items = new Item;

		return $items->rebuild(1);
	}

	/**
	 * Gets a list of all mod_mainmenu modules and collates them by menutype
	 *
	 * @return  array<string,array>
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
			$params = new \Illuminate\Config\Repository(json_decode($widget->params, true));

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
	 * @param  string $type
	 * @param  array  $columns
	 * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
	 */
	public static function findByMenutype(string $type, array $columns = ['*'])
	{
		return static::query()->where('menutype', '=', $type)->first($columns);
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @param   array  $options
	 * @return  bool   False if error, True on success
	 */
	public function save(array $options = [])
	{
		if ($this->id)
		{
			// Get the old value of the table just in case the 'menutype' changed
			$prev = self::query()->withTrashed()->where('id', '=', $this->id)->first();

			if ($prev && ($this->menutype != $prev->menutype)) //$prev->getOriginal('menutype'))
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
					throw new \Exception(
						trans('core::core.error.save failed', get_class($this), trans('core::core.error.failed to checkout menu'))
					);
				}

				// Verify that no module for this menu are checked out
				$checked_out = $prev->widgets()
					->where('checked_out', '!=', (int) $userId)
					->where('checked_out', '!=', 0)
					->count();

				if ($checked_out)
				{
					throw new \Exception(
						trans('core::core.error.save failed', get_class($this), trans('core::core.error.failed to checkout menu'))
					);
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
						return false;
					}
				}*/

				// Update the module items
				foreach ($prev->widgets()->get() as $widget)
				{
					$widget->params->menutype = $this->menutype;
					//$widget->params = $widget->params->toString();

					if (!$widget->save())
					{
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
	 * @return  bool  False if error, True on success
	 */
	public function delete()
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
			throw new \Exception(
				trans('global.error.delete failed', get_class($this), trans('menus::menus.error.checked out'))
			);
		}

		// Verify that no module for this menu are checked out
		$checked_out = $this->widgets()
			->where('checked_out', '!=', (int) $userId)
			->where('checked_out', '!=', 0)
			->count();

		if ($checked_out)
		{
			throw new \Exception(
				trans('global.error.delete failed', get_class($this), trans('menus::menus.error.checked out'))
			);
		}

		// Delete the menu items
		foreach ($this->items as $item)
		{
			$item->delete();
		}

		// Delete the module items
		foreach ($this->widgets() as $module)
		{
			$module->delete();
		}

		// Attempt to delete the record
		return parent::delete();
	}
}
