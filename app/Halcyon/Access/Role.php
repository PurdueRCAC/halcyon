<?php

namespace App\Halcyon\Access;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * User role
 *
 * @property int    $id
 * @property int    $parent_id
 * @property int    $lft
 * @property int    $rgt
 * @property string $title
 */
class Role extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 */
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 */
	protected $table = 'user_roles';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public static $orderBy = 'lft';

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
		'id'
	];

	/**
	 * The "booted" method of the model.
	 *
	 * @return void
	 * @throws \Exception
	 */
	protected static function booted()
	{
		static::creating(function ($model)
		{
			$exist = self::query()
				->where('title', '=', $model->title)
				->where('parent_id', '=', $model->parent_id)
				->count();

			if ($exist)
			{
				throw new \Exception('Role already exists.');
			}
		});
	}

	/**
	 * Defines a relationship to the User/Role Map
	 *
	 * @return  HasMany
	 */
	public function maps(): HasMany
	{
		return $this->hasMany(Map::class, 'role_id');
	}

	/**
	 * Get parent
	 *
	 * @return  BelongsTo
	 */
	public function parent(): BelongsTo
	{
		return $this->belongsTo(self::class, 'parent');
	}

	/**
	 * Count descendents
	 *
	 * @return  int
	 */
	public function countDescendents(): int
	{
		return static::query()
				->where('lft', '<', $this->lft)
				->where('rgt', '>', $this->rgt)
				->count();
	}

	/**
	 * Load a record by title
	 *
	 * @param   string  $title
	 * @return  Role|null
	 */
	public static function findByTitle($title)
	{
		return self::query()
			->where('title', '=', $title)
			->first();
	}

	/**
	 * Perform any actions that are necessary after the model is saved.
	 *
	 * @param  array  $options
	 * @return void
	 */
	protected function finishSave(array $options): void
	{
		$this->rebuild();

		parent::finishSave($options);
	}

	/**
	 * Method to recursively rebuild the nested set tree.
	 *
	 * @param   int  $parent_id  The root of the tree to rebuild.
	 * @param   int  $left       The left id to start with in building the tree.
	 * @return  int
	 */
	public function rebuild($parent_id = 0, $left = 0): int
	{
		// get all children of this node
		$children = self::query()
			->select('id')
			->where('parent_id', '=', (int) $parent_id)
			->orderBy('parent_id', 'asc')
			->get();

		// the right value of this node is the left value + 1
		$right = $left + 1;

		// execute this function recursively over all children
		foreach ($children as $child)
		{
			// $right is the current right value, which is incremented on recursion return
			$right = $this->rebuild($child->id, $right);
		}

		// we've got the left value, and now that we've processed
		// the children of this node we also know the right value
		$result = self::query()
			->where('id', '=', (int) $parent_id)
			->update(array(
				'lft' => (int) $left,
				'rgt' => (int) $right
			));

		// return the right value of this node + 1
		return $right + 1;
	}

	/**
	 * Delete the model from the database.
	 *
	 * @return bool
	 */
	public function delete(): bool
	{
		if ($this->id == 0)
		{
			return false;
		}

		if ($this->parent_id == 0)
		{
			return false;
		}

		if ($this->lft == 0 or $this->rgt == 0)
		{
			return false;
		}

		// Select it's children
		$children = self::query()
			->where('lft', '>=', (int)$this->lft)
			->where('rgt', '<=', (int)$this->rgt)
			->get();

		if (!$children->count())
		{
			return false;
		}

		// Delete the dependencies
		$ids = array();

		foreach ($children as $child)
		{
			$ids[] = $child->id;
		}

		$result = self::query()
			->whereIn('id', $ids)
			->delete();

		if (!$result)
		{
			return false;
		}

		// Delete the role in view levels
		$find    = array();
		$replace = array();
		foreach ($ids as $id)
		{
			$find[] = "[$id,";
			$find[] = ",$id,";
			$find[] = ",$id]";
			$find[] = "[$id]";

			$replace[] = "[";
			$replace[] = ",";
			$replace[] = "]";
			$replace[] = "[]";
		}

		$rules = Viewlevel::all();

		foreach ($rules as $rule)
		{
			foreach ($ids as $id)
			{
				if (strstr($rule->rules, '[' . $id)
				 || strstr($rule->rules, ',' . $id)
				 || strstr($rule->rules, $id . ']'))
				{
					$rule->rules = str_replace($find, $replace, $rule->rules);

					if (!$rule->save())
					{
						return false;
					}
				}
			}
		}

		// Delete the user to user role mappings for the role(s) from the database.
		try
		{
			Map::destroyByRole($ids);
		}
		catch (\Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to get the whole roles tree
	 *
	 * @return  Collection
	 */
	public static function tree()
	{
		$model = new self;
		$map = new Map;

		$results = DB::table($model->getTable() . ' AS a')
			->select(
				'a.id AS value',
				'a.title AS text',
				DB::raw('COUNT(DISTINCT b.id) AS level'),
				'a.parent_id',
				DB::raw('COUNT(DISTINCT m.user_id) AS maps_count')
			)
			->leftJoin($model->getTable() . ' AS b', function ($join)
			{
				$join->on('a.lft', '>', 'b.lft')
					->on('a.rgt', '<', 'b.rgt');
			})
			->leftJoin($map->getTable() . ' AS m', 'm.role_id', 'a.id')
			->groupBy(
				'a.id',
				'a.title',
				'a.lft',
				'a.rgt',
				'a.parent_id'
			)
			->orderBy('a.lft', 'asc')
			->get();

		return $results;
	}
}
