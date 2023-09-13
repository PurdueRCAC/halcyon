<?php
namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Modules\History\Traits\Historable;

/**
 * Group department
 *
 * @property int    $id
 * @property int    $parentid
 * @property string $name
 *
 * @property string $api
 * @property int    $level
 * @property string $prefix
 * @property int    $groups_count
 */
class Department extends Model
{
	use Historable;

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
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted(): void
	{
		static::creating(function ($model)
		{
			$result = self::query()
				->select(DB::raw('MAX(id) + 1 AS seq'))
				->value('seq');

			$model->setAttribute('id', (int)$result);
		});

		static::deleting(function ($model)
		{
			// Remove all group associations
			foreach ($model->groups as $row)
			{
				$row->delete();
			}

			// Remove children
			foreach ($model->children as $row)
			{
				$row->delete();
			}
		});
	}

	/**
	 * Get records as nested tree
	 *
	 * @param   string  $order
	 * @param   string  $dir
	 * @return  array<int,Department>
	 */
	public static function tree(string $order = 'name', string $dir = 'asc'): array
	{
		$rows = self::query()
			->withCount('groups')
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

				if (!isset($children[$pt]))
				{
					$children[$pt] = array();
				}
				$children[$pt][] = $k;
			}

			// Second pass - get an indent list of the items
			$list = self::treeRecurse(0, $list, $children, max(0, $levellimit-1));
		}

		return $list;
	}

	/**
	 * Recursive function to build tree
	 *
	 * @param   int    $id        Parent ID
	 * @param   array<int,Department>  $list      List of records
	 * @param   array<int,array<int,Department>>  $children  Container for parent/children mapping
	 * @param   int    $maxlevel  Maximum levels to descend
	 * @param   int    $level     Indention level
	 * @param   string $prfx
	 * @return  array<int,Department>
	 */
	protected static function treeRecurse(int $id, array $list, array $children, int $maxlevel=9999, int $level=0, int $type=1, string $prfx = ''): array
	{
		if (@$children[$id] && $level <= $maxlevel)
		{
			foreach ($children[$id] as $z => $v)
			{
				$vid = $v->id;
				$pt = $v->parentid;

				$list[$vid] = $v;
				$list[$vid]->prefix = ($prfx ? $prfx . ' › ' : '');
				$list[$vid]->name = $list[$vid]->name;
				$list[$vid]->level = $level;
				$list[$vid]->children_count = isset($children[$vid]) ? count(@$children[$vid]) : 0;

				$p = '';
				if ($v->parentid)
				{
					$p = $list[$vid]->prefix . $list[$vid]->name;
				}

				unset($children[$id][$z]);

				$list = self::treeRecurse($vid, $list, $children, $maxlevel, $level+1, $type, $p);
			}
			unset($children[$id]);
		}
		return $list;
	}

	/**
	 * Parent
	 *
	 * @return  BelongsTo
	 */
	public function parent(): BelongsTo
	{
		return $this->belongsTo(self::class, 'parentid');
	}

	/**
	 * Get all parents
	 *
	 * @param   array<int,Department>  $ancestors
	 * @return  array<int,Department>
	 */
	public function ancestors(array $ancestors = []): array
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
	 * Relationship to child records
	 *
	 * @return  HasMany
	 */
	public function children(): HasMany
	{
		return $this->hasMany(self::class, 'parentid');
	}

	/**
	 * Groups
	 *
	 * @return  HasMany
	 */
	public function groups(): HasMany
	{
		return $this->hasMany(GroupDepartment::class, 'collegedeptid');
		//return $this->hasOneThrough(GroupFieldOfScience::class, GroupDepartment::class, 'groupid', 'id', 'groupid', 'collegedeptid');
	}

	/**
	 * Query scope with search
	 *
	 * @param   Builder  $query
	 * @param   string  $search
	 * @return  Builder
	 */
	public function scopeWhereSearch(Builder $query, $search): Builder
	{
		if (is_numeric($search))
		{
			$query->where('id', '=', $search);
		}
		else
		{
			$query->where(function ($where) use ($search)
			{
				$search = strtolower((string)$search);
				$skipmiddlename = preg_replace('/ /', '% ', $search);

				$where->where('name', 'like', '% ' . $search . '%')
					->orWhere('name', 'like', $search . '%')
					->orWhere('name', 'like', '% ' . $skipmiddlename . '%')
					->orWhere('name', 'like', $skipmiddlename . '%');
			});
		}

		return $query;
	}
}
