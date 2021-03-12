<?php
namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;

/**
 * Group department
 */
class Department extends Model
{
	use ErrorBag, Validatable, Historable;

	/**
	 * Timestamps
	 *
	 * @var  bool
	 **/
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'collegedept';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'name';

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
		'id'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'name' => 'required'
	);

	/**
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted()
	{
		static::creating(function ($model)
		{
			$result = self::query()
				->select(DB::raw('MAX(id) + 1 AS seq'))
				->get()
				->first()
				->seq;

			$model->setAttribute('id', (int)$result);
		});
	}

	/**
	 * Defines a relationship to parent
	 *
	 * @return  object
	 */
	public static function tree($order = 'name', $dir = 'asc')
	{
		$rows = self::query()
			->orderBy($order, $dir)
			->get();

		$list = array();

		if (count($rows) > 0)
		{
			$levellimit = 9999;
			$list       = array();
			$children   = array();

			// First pass - collect children
			foreach ($rows as $k)
			{
				$pt = $k->parentid;
				$list = @$children[$pt] ? $children[$pt] : array();
				array_push($list, $k);
				$children[$pt] = $list;
			}

			// Second pass - get an indent list of the items
			$list = self::treeRecurse(0, array(), $children, max(0, $levellimit-1));
		}

		return $list;
	}

	/**
	 * Recursive function to build tree
	 *
	 * @param   integer  $id        Parent ID
	 * @param   array    $list      List of records
	 * @param   array    $children  Container for parent/children mapping
	 * @param   integer  $maxlevel  Maximum levels to descend
	 * @param   integer  $level     Indention level
	 * @param   integer  $type      Indention type
	 * @param   string   $prfx
	 * @return  array
	 */
	protected static function treeRecurse($id, $list, $children, $maxlevel=9999, $level=0, $type=1, $prfx = '')
	{
		if (@$children[$id] && $level <= $maxlevel)
		{
			foreach ($children[$id] as $v)
			{
				$id = $v->id;
				$pt = $v->parentid;

				$list[$id] = $v;
				$list[$id]->prefix = ($prfx ? $prfx . ' › ' : '');
				$list[$id]->name = $list[$id]->name;
				$list[$id]->level = $level;
				$list[$id]->children = isset($children[$id]) ? count(@$children[$id]) : 0;

				$p = '';
				if ($v->parentid)
				{
					$p = $list[$id]->prefix . $list[$id]->name;
				}
				//$prfx = $list[$id]->name;

				$list = self::treeRecurse($id, $list, $children, $maxlevel, $level+1, $type, $p);
			}
		}
		return $list;
	}

	/**
	 * Parent
	 *
	 * @return  object
	 */
	public function parent()
	{
		return $this->belongsTo(self::class, 'parentid');
	}

	/**
	 * Get all parents
	 *
	 * @param   array  $ancestors
	 * @return  array
	 */
	public function ancestors($ancestors = array())
	{
		$parent = $this->parent;

		if ($parent)
		{
			if ($parent->parentid)
			{
				$ancestors = $parent->ancestors($ancestors);
			}

			$ancestors[] = $parent;
		}

		return $ancestors;
	}

	/**
	 * Groups
	 *
	 * @return  object
	 */
	public function children()
	{
		return $this->hasMany(self::class, 'parentid');
	}

	/**
	 * Groups
	 *
	 * @return  object
	 */
	public function groups()
	{
		return $this->hasMany(GroupDepartment::class, 'collegedeptid');
		//return $this->hasOneThrough(GroupFieldOfScience::class, GroupDepartment::class, 'groupid', 'id', 'groupid', 'collegedeptid');
	}

	/**
	 * Delete entry and associated data
	 *
	 * @param   array  $options
	 * @return  bool
	 */
	public function delete(array $options = [])
	{
		foreach ($this->children as $row)
		{
			$row->delete();
		}

		foreach ($this->groups as $row)
		{
			$row->delete();
		}

		return parent::delete($options);
	}
}
