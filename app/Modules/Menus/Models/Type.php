<?php

namespace App\Modules\Menus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
 *
 * @property string $api
 * @property array<int,Item> $links
 * @property array<string,int> $counts
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
		$value = Str::limit($value, 24);

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

		// Clear any cached admin menu widgets
		// whenever a menu is added or deleted
		// because the widget lists available menus.
		static::created(function ($model)
		{
			$model->clearCachedWidgets();
		});

		// Ensure the menutype is unique
		static::creating(function ($model)
		{
			do
			{
				$exist = self::query()
					->where('menutype', '=', $model->menutype)
					->where('id', '!=', $model->id)
					->first();
				if ($exist)
				{
					if (strlen($model->menutype) > 45)
					{
						$model->menutype = Str::limit($model->menutype, 45);
					}
					$model->menutype = $model->menutype . rand(0, 100);
				}
			}
			while ($exist);
		});

		static::deleted(function ($model)
		{
			$model->clearCachedWidgets();
		});
	}

	/**
	 * Clear any related widget caches
	 *
	 * @return void
	 */
	public function clearCachedWidgets(): void
	{
		$widgets = Widget::query()
			->where('widget', '=', 'Adminmenu')
			->get();

		if (count($widgets))
		{
			foreach ($widgets as $widget)
			{
				if (Cache::has($widget->cacheKey()))
				{
					Cache::forget($widget->cacheKey());
				}
			}
		}
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
	 * @return  Collection
	 */
	public function getTreeAttribute(): Collection
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
	 * @return  Builder
	 */
	public function widgets(): Builder
	{
		$query = Widget::query()
			->where('widget', '=', 'Menu')
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
	 * @return  int|false
	 */
	public function rebuild()
	{
		// Initialiase variables.
		$root = Item::rootNode();

		return $root->rebuild($root->id);
	}

	/**
	 * Gets a list of all menu widgets and collates them by menutype
	 *
	 * @return  array<string,array<int,Widget>>
	 */
	public static function getWidgets()
	{
		$widgets = Widget::query()
			->where('widget', '=', 'menu')
			->get();

		$result = array();

		foreach ($widgets as $widget)
		{
			$menuType = $widget->params->get('menutype');
			$menuType = $menuType ?: '_unknown_';
			if (!isset($result[$menuType]))
			{
				$result[$menuType] = array();
			}
			$result[$menuType][] = $widget;
		}

		return $result;
	}

	/**
	 * Find a model by menutype
	 *
	 * @param  string $type
	 * @return Type|null
	 */
	public static function findByMenutype(string $type): ?Type
	{
		return static::query()
			->where('menutype', '=', $type)
			->first();
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @param   array<string,mixed>  $options
	 * @return  bool   False if error, True on success
	 */
	public function save(array $options = []): bool
	{
		if ($this->id)
		{
			// Get the old value of the table just in case the 'menutype' changed
			$prev = self::query()
				->withTrashed()
				->where('id', '=', $this->id)
				->first();

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
						trans('core::core.error.save failed', ['class' => get_class($this), 'error' => trans('core::core.error.failed to checkout menu')])
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
						trans('core::core.error.save failed', ['class' => get_class($this), 'error' => trans('core::core.error.failed to checkout menu')])
					);
				}

				// Update the menu items
				DB::table((new Item)->getTable())
					->where('menutype', '=', $prev->menutype)
					->update(['menutype' => $this->menutype]);

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
	 * @throws  \Exception
	 */
	public function delete(): bool
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
				trans('global.error.delete failed', ['class' => get_class($this), 'error' => trans('menus::menus.error.checked out')])
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
				trans('global.error.delete failed', ['class' => get_class($this), 'error' => trans('menus::menus.error.checked out')])
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
