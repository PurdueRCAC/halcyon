<?php

namespace App\Halcyon\Access;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * User role
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
	 * Fields and their validation criteria
	 *
	 * @var  array<string,string>
	 */
	protected $rules = array(
		'title' => 'required|string|max:100'
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Sets up additional custom rules
	 *
	 * @return  void
	 */
		/**
	 * The "booted" method of the model.
	 *
	 * @return void
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
	 * @return  object
	 */
	public function maps()
	{
		return $this->hasMany(Map::class, 'role_id');
	}

	/**
	 * Get parent
	 *
	 * @return  object
	 */
	public function parent()
	{
		return $this->belongsTo(self::class, 'parent');
	}

	/**
	 * Count descendents
	 *
	 * @return  integer
	 */
	public function countDescendents()
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
	protected function finishSave(array $options)
	{
		$this->rebuild();

		return parent::finishSave($options);
	}

	/**
	 * Method to recursively rebuild the nested set tree.
	 *
	 * @param   integer  $parent_id  The root of the tree to rebuild.
	 * @param   integer  $left       The left id to start with in building the tree.
	 * @return  integer
	 */
	public function rebuild($parent_id = 0, $left = 0)
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

			// if there is an update failure, return false to break out of the recursion
			/*if ($right === false)
			{
				return false;
			}*/
		}

		// we've got the left value, and now that we've processed
		// the children of this node we also know the right value
		$result = self::query()
			->where('id', '=', (int) $parent_id)
			->update(array(
				'lft' => (int) $left,
				'rgt' => (int) $right
			));

		// if there is an update failure, return false to break out of the recursion
		/*if (!$result)
		{
			return false;
		}*/

		// return the right value of this node + 1
		return $right + 1;
	}

	/**
	 * Delete the model from the database.
	 *
	 * @return bool|null
	 */
	public function delete()
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
	 * @return  object
	 */
	public static function tree()
	{
		$model = new self;
		$map = new Map;

		$results = DB::table($model->getTable() . ' AS a')
			->select('a.id AS value', 'a.title AS text', DB::raw('COUNT(DISTINCT b.id) AS level'), 'a.parent_id', DB::raw('COUNT(DISTINCT m.user_id) AS maps_count'))
			->leftJoin($model->getTable() . ' AS b', function($join)
			{
				$join->on('a.lft', '>', 'b.lft')
					->on('a.rgt', '<', 'b.rgt');
			})
			->leftJoin($map->getTable() . ' AS m', 'm.role_id', 'a.id')
			->groupBy('a.id', 'a.title', 'a.lft', 'a.rgt', 'a.parent_id')
			->orderBy('a.lft', 'asc')
			->get();

		return $results;
	}
}
