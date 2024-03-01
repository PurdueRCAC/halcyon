<?php

namespace App\Modules\Knowledge\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Model for a page association mapping
 *
 * @property int    $id
 * @property int    $parent_id
 * @property int    $page_id
 * @property int    $lft
 * @property int    $rgt
 * @property int    $level
 * @property string $path
 * @property int    $state
 * @property int    $access
 *
 * @property string $alias Page alias
 */
class Associations extends Model
{
	/**
	 * States
	 **/
	const STATE_UNPUBLISHED = 0;
	const STATE_PUBLISHED   = 1;
	const STATE_ARCHIVED    = 2;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'kb_page_associations';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'lft';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * Defines a relationship to a parent page
	 *
	 * @return  int
	 */
	public function getUsedAttribute(): int
	{
		$root = self::rootNode();

		return self::query()
			->where('page_id', '=', $this->page_id)
			->where('lft', '>', $root->lft)
			->where('rgt', '<', $root->rgt)
			->count();
	}

	/**
	 * Is this a separator
	 *
	 * @return  bool
	 */
	public function isSeparator(): bool
	{
		return (substr($this->path, -strlen('-separator-')) == '-separator-');
	}

	/**
	 * Defines a relationship to a parent page
	 *
	 * @return  BelongsTo
	 */
	public function parent(): BelongsTo
	{
		return $this->belongsTo(self::class, 'parent_id');
	}

	/**
	 * Defines a relationship to a page
	 *
	 * @return  BelongsTo
	 */
	public function page(): BelongsTo
	{
		return $this->belongsTo(Page::class, 'page_id')->withTrashed();
	}

	/**
	 * Defines a relationship to child pages
	 *
	 * @return  HasMany
	 */
	public function children(): HasMany
	{
		return $this->hasMany(self::class, 'parent_id');
	}

	/**
	 * Defines a relationship to feedback
	 *
	 * @return  HasMany
	 */
	public function feedback(): HasMany
	{
		return $this->hasMany(Feedback::class, 'target_id');
	}

	/**
	 * Get published children
	 *
	 * @return  Collection
	 */
	public function publishedChildren(): Collection
	{
		return $this->children()
			->where('state', '=', self::STATE_PUBLISHED)
			->whereIn('access', (auth()->user() ? auth()->user()->getAuthorisedViewLevels() : [1]))
			->orderBy('lft', 'asc')
			->get();
		/*$p = (new Page)->getTable();

		return $this->children()
			->select($this->getTable() . '.*')
			->join($p, $p . '.id', $this->getTable() . '.page_id')
			//->orderBy($a . '.lft', 'asc')
			->where($p . '.state', '=', self::STATE_PUBLISHED)
			->whereIn($p . '.access', (auth()->user() ? auth()->user()->getAuthorisedViewLevels() : [1]))
			->get();*/
	}

	/**
	 * Get all descendant pages
	 *
	 * @return  Builder
	 */
	public function descendants(): Builder
	{
		return self::query()
			->where('lft', '>', $this->lft)
			->where('rgt', '<', $this->rgt)
			->orderBy('lft', 'asc');
	}

	/**
	 * Get the root node
	 *
	 * @return  Associations|null
	 */
	public static function rootNode(): ?Associations
	{
		return self::query()
			->where('level', '=', 0)
			//->where('path', '=', 'ROOT')
			->orderBy('lft', 'asc')
			->limit(1)
			->first();
	}

	/**
	 * Find a page by URI path
	 *
	 * @param   string  $path
	 * @return  Associations|null
	 */
	public static function findByPath(string $path): ?Associations
	{
		$path = trim($path, '/');

		$a = (new self)->getTable();
		$p = (new Page)->getTable();

		return self::query()
			->select($a . '.*')
			->join($p, $p . '.id', $a . '.page_id')
			->where($a . '.path', '=', $path)
			->whereNull($p . '.deleted_at')
			->orderBy($a . '.state', 'desc') // We want published first
			->limit(1)
			->first();
	}

	/**
	 * Get the full page stack by path
	 *
	 * @param   string  $path
	 * @return  array<int,Associations>|bool
	 */
	public static function stackByPath(string $path)
	{
		$path = trim($path, '/');

		$parent = self::rootNode();
		$stack = array();
		$stack[] = $parent;

		if (!$path)
		{
			return $stack;
		}

		$segments = explode('/', $path);
		if (isset($segments[0]) && $segments[0] == 'knowledge')
		{
			array_shift($segments);
		}

		if (empty($segments))
		{
			return $stack;
		}

		$tbl = (new self)->getTable();

		$results = DB::table($tbl . ' AS a')
			->select('b.*')
			->leftJoin($tbl . ' AS b', function ($join)
			{
				$join->on('b.lft', '<=', 'a.lft')
					->on('b.rgt', '>=', 'a.rgt');
			})
			->where('a.path', '=', implode('/', $segments))
			->where('b.level', '>', 0) // We already have the root node
			->orderBy('b.lft', 'asc')
			->get();

		if (count($results))
		{
			foreach (self::hydrate($results->toArray()) as $result)
			{
				$stack[] = $result;
			}
		}

		/*$p = '';
		foreach ($segments as $segment)
		{
			$p .= $p ? '/' . $segment : $segment;

			$child = self::findByPath($p);

			if (!$child)
			{
				return false;
			}

			//$child->variables->merge($parent->variables);

			$stack[] = $child;

			//$parent = $child;
		}*/

		if ((count($stack) - 1) != count($segments))
		{
			return false;
		}

		return $stack;
	}

	/**
	 * Determine if record is the root page
	 * 
	 * @return  bool
	 */
	public function isRoot(): bool
	{
		return ($this->level == 0);
	}

	/**
	 * Determine if record is published
	 * 
	 * @return  bool
	 */
	public function isPublished(): bool
	{
		return ($this->state == self::STATE_PUBLISHED);
	}

	/**
	 * Determine if record is archived
	 * 
	 * @return  bool
	 */
	public function isArchived(): bool
	{
		return ($this->state == self::STATE_ARCHIVED);
	}

	/**
	 * Get all aprents
	 *
	 * @param   array<int,Associations>  $ancestors
	 * @return  array<int,Associations>
	 */
	public function ancestors(array $ancestors = []): array
	{
		$parent = $this->parent;

		if ($parent && $parent->level > 0)
		{
			$ancestors[] = $parent;

			if ($parent->parent_id)
			{
				$ancestors = $parent->ancestors($ancestors);
			}
		}

		return $ancestors;
	}

	/**
	 * Save the record
	 *
	 * @param   array<string,mixed>    $options
	 * @return  bool     False if error, True on success
	 */
	public function save(array $options = []): bool
	{
		$isNew = !$this->id;

		$parent = $this->parent;

		if ($isNew)
		{
			if (!$this->parent_id)
			{
				$root = self::rootNode();

				$this->lft = $root->lft + 1;
				$this->rgt = $root->lft + 2;
				$this->parent_id = $root->id;

				$parent = $this->parent;
			}

			if (!$parent || !$parent->id)
			{
				throw new \Exception(trans('Parent node does not exist.'));
			}

			if (!$this->access)
			{
				$this->access = 1;
			}

			// Get the reposition data for shifting the tree and re-inserting the node.
			$reposition = $this->getTreeRepositionData($parent, 2, 'last-child');

			// Shift left values.
			$query = DB::table($this->getTable())
				->where($reposition->left_where['col'], $reposition->left_where['op'], $reposition->left_where['val'])
				->update(['lft' => DB::raw('lft + 2')]);

			// Shift right values.
			$query = DB::table($this->getTable())
				->where($reposition->right_where['col'], $reposition->right_where['op'], $reposition->right_where['val'])
				->update(['rgt' => DB::raw('rgt + 2')]);

			// Set all the nested data
			$path = array();

			/*foreach ($this->ancestors() as $ancestor)
			{
				$path[] = $ancestor->page->alias;
			}*/
			if ($parent->path !== 'ROOT')
			{
				$path[] = $parent->path;
			}

			$path[] = $this->page->alias;
			$path = implode('/', $path);
			$path = trim($path, '/');

			$this->setAttribute('path', $path);
			$this->setAttribute('lft', $reposition->new_lft);
			$this->setAttribute('rgt', $reposition->new_rgt);
		}

		if ($parent)
		{
			$this->level = $parent->level + 1;
		}
		else
		{
			$this->level = 0;
		}

		return parent::save($options);
	}

	/**
	 * Method to recursively rebuild the whole nested set tree.
	 *
	 * @param   int  $parentId  The root of the tree to rebuild.
	 * @param   int  $leftId    The left id to start with in building the tree.
	 * @param   int  $level     The level to assign to the current nodes.
	 * @param   string   $path      The path to the current nodes.
	 * @return  int  1 + value of root rgt on success, false on failure
	 */
	public function rebuild(int $parentId, int $leftId = 0, int $level = 0, string $path = ''): int
	{
		$a = $this->getTable();
		$p = (new Page)->getTable();

		// Assemble the query to find all children of this node.
		$children = self::query()
			->select($a . '.id', $p . '.alias')
			->join($p, $p . '.id', $a . '.page_id')
			->where($a . '.parent_id', '=', (int) $parentId)
			->orderBy($a . '.parent_id', 'asc')
			->orderBy($a . '.lft', 'asc')
			->get();

		// The right value of this node is the left value + 1
		$rightId = $leftId + 1;

		// execute this function recursively over all children
		foreach ($children as $node)
		{
			// $rightId is the current right value, which is incremented on recursion return.
			// Increment the level for the children.
			// Add this item's alias to the path (but avoid a leading /)
			$rightId = $this->rebuild(
				$node->id,
				$rightId,
				$level + 1,
				$path . (empty($path) ? '' : '/') . $node->alias
			);

			// If there is an update failure, return false to break out of the recursion.
			if ($rightId === false)
			{
				return 0;
			}
		}

		// We've got the left value, and now that we've processed
		// the children of this node we also know the right value.
		DB::table($this->getTable())
			->where('id', '=', (int) $parentId)
			->update(array(
				'lft'   => (int) $leftId,
				'rgt'   => (int) $rightId,
				'level' => (int) $level,
				'path'  => $path
			));

		// Return the right value of this node + 1.
		return $rightId + 1;
	}

	/**
	 * Method to get various data necessary to make room in the tree at a location
	 * for a node and its children.  The returned data object includes conditions
	 * for SQL WHERE clauses for updating left and right id values to make room for
	 * the node as well as the new left and right ids for the node.
	 *
	 * @param   object   $referenceNode  A node object with at least a 'lft' and 'rgt' with
	 *                                   which to make room in the tree around for a new node.
	 * @param   int  $nodeWidth      The width of the node for which to make room in the tree.
	 * @param   string   $position       The position relative to the reference node where the room
	 *                                   should be made.
	 * @return  bool|\stdClass    Boolean false on failure or data object on success.
	 */
	protected function getTreeRepositionData($referenceNode, int $nodeWidth, string $position = 'before')
	{
		// Make sure the reference an object with a left and right id.
		if (!is_object($referenceNode) && isset($referenceNode->lft) && isset($referenceNode->rgt))
		{
			return false;
		}

		// A valid node cannot have a width less than 2.
		if ($nodeWidth < 2)
		{
			return false;
		}

		// Initialise variables.
		$k = 'id';

		$data = new \stdClass;

		// Run the calculations and build the data object by reference position.
		switch ($position)
		{
			case 'first-child':
				$data->left_where  = array('col' => 'lft', 'op' => '>', 'val' => $referenceNode->lft);
				$data->right_where = array('col' => 'rgt', 'op' => '>=', 'val' => $referenceNode->lft);

				$data->new_lft = $referenceNode->lft + 1;
				$data->new_rgt = $referenceNode->lft + $nodeWidth;
				$data->new_parent_id = $referenceNode->$k;
				$data->new_level = $referenceNode->level + 1;
			break;

			case 'last-child':
				$data->left_where  = array('col' => 'lft', 'op' => '>', 'val' => $referenceNode->rgt);
				$data->right_where = array('col' => 'rgt', 'op' => '>=', 'val' => $referenceNode->rgt);

				$data->new_lft = $referenceNode->rgt;
				$data->new_rgt = $referenceNode->rgt + $nodeWidth - 1;
				$data->new_parent_id = $referenceNode->$k;
				$data->new_level = $referenceNode->level + 1;
			break;

			case 'before':
				$data->left_where  = array('col' => 'lft', 'op' => '>=', 'val' => $referenceNode->lft);
				$data->right_where = array('col' => 'rgt', 'op' => '>=', 'val' => $referenceNode->lft);

				$data->new_lft = $referenceNode->lft;
				$data->new_rgt = $referenceNode->lft + $nodeWidth - 1;
				$data->new_parent_id = $referenceNode->parent_id;
				$data->new_level = $referenceNode->level;
			break;

			default:
			case 'after':
				$data->left_where  = array('col' => 'lft', 'op' => '>', 'val' => $referenceNode->rgt);
				$data->right_where = array('col' => 'rgt', 'op' => '>', 'val' => $referenceNode->rgt);

				$data->new_lft = $referenceNode->rgt + 1;
				$data->new_rgt = $referenceNode->rgt + $nodeWidth;
				$data->new_parent_id = $referenceNode->parent_id;
				$data->new_level = $referenceNode->level;
			break;
		}

		return $data;
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @return  bool  False if error, True on success
	 */
	public function delete(): bool
	{
		foreach ($this->children as $row)
		{
			$row->delete();
		}

		// Attempt to delete the record
		return parent::delete();
	}

	/**
	 * Method to move a row in the ordering sequence of a group of rows defined by an SQL WHERE clause.
	 * Negative numbers move the row up in the sequence and positive numbers move it down.
	 *
	 * @param   int     $delta  The direction and magnitude to move the row in the ordering sequence.
	 * @param   string  $where  WHERE clause to use for limiting the selection of rows to compact the ordering values.
	 * @return  bool    Boolean true on success.
	 * @throws \Exception
	 */
	public function move($delta, string $where = ''): bool
	{
		$query = self::query()
			->where('parent_id', '=', $this->parent_id);

		/*if ($where)
		{
			$query->whereRaw($where);
		}*/

		$position = 'after';

		if ($delta > 0)
		{
			$query->where('rgt', '>', $this->rgt);
			$query->orderBy('rgt', 'asc');
			$position = 'after';
		}
		else
		{
			$query->where('lft', '<', $this->lft);
			$query->orderBy('lft', 'desc');
			$position = 'before';
		}

		$referenceId = $query->first()->id;

		if ($referenceId)
		{
			return $this->moveByReference($referenceId, $position, $this->id);
		}

		throw new \Exception(trans('global.error.move failed') . ': Reference not found for delta ' . $delta);
	}

	/**
	 * Method to move a node and its children to a new location in the tree.
	 *
	 * @param   int     $referenceId  The primary key of the node to reference new location by.
	 * @param   string  $position     Location type string. ['before', 'after', 'first-child', 'last-child']
	 * @param   int     $pk           The primary key of the node to move.
	 * @return  bool    True on success.
	 * @throws \Exception
	 */
	public function moveByReference(int $referenceId, string $position = 'after', int $pk = 0)
	{
		// Initialise variables.
		$pk = $pk ?: $this->id;

		// Get the node by id.
		$node = self::find($pk);

		if (!$node->id)
		{
			// Error message set in getNode method.
			throw new \Exception(trans('global.error.move failed') . ': Node not found #' . $pk);
		}

		// Get the ids of child nodes.
		$children = self::query()
			->whereBetween('lft', [(int) $node->lft, (int) $node->rgt])
			->get()
			->pluck('id')
			->toArray();

		// Cannot move the node to be a child of itself.
		if (in_array($referenceId, $children))
		{
			throw new \Exception(trans('global.error.invalid node recursion'));
		}

		// Move the sub-tree out of the nested sets by negating its left and right values.
		self::query()
			->whereBetween('lft', [(int) $node->lft, (int) $node->rgt])
			->update(array(
				'lft' => DB::raw('lft * (-1)'),
				'rgt' => DB::raw('rgt * (-1)')
			));

		// Close the hole in the tree that was opened by removing the sub-tree from the nested sets.

		// Compress the left values.
		self::query()
			->where('lft', '>', (int) $node->rgt)
			->update(array(
				'lft' => DB::raw('lft - ' . (int) ($node->rgt - $node->lft + 1))
			));

		// Compress the right values.
		self::query()
			->where('rgt', '>', (int) $node->rgt)
			->update(array(
				'rgt' => DB::raw('rgt - ' . (int) ($node->rgt - $node->lft + 1))
			));

		// We are moving the tree relative to a reference node.
		if ($referenceId)
		{
			// Get the reference node by primary key.
			$reference = self::find($referenceId);

			if (!$reference)
			{
				throw new \Exception(trans('global.error.move failed') . ': Reference not found #' . $referenceId);
			}

			// Get the reposition data for shifting the tree and re-inserting the node.
			$repositionData = $this->getTreeRepositionData($reference, ($node->rgt - $node->lft + 1), $position);
		}
		// We are moving the tree to be the last child of the root node
		else
		{
			// Get the last root node as the reference node.
			$reference = self::query()
				->select(['id', 'parent_id', 'level', 'lft', 'rgt'])
				->where('parent_id', '=', 0)
				->orderBy('lft', 'DESC')
				->first();

			// Get the reposition data for re-inserting the node after the found root.
			$repositionData = $this->getTreeRepositionData($reference, ($node->rgt - $node->lft + 1), 'last-child');
		}

		// Create space in the nested sets at the new location for the moved sub-tree.

		// Shift left values.
		self::query()
			->where($repositionData->left_where['col'], $repositionData->left_where['op'], $repositionData->left_where['val'])
			->update(array(
				'lft' => DB::raw('lft + ' . (int) ($node->rgt - $node->lft + 1))
			));

		// Shift right values.
		self::query()
			->where($repositionData->right_where['col'], $repositionData->right_where['op'], $repositionData->right_where['val'])
			->update(array(
				'rgt' => DB::raw('rgt + ' . (int) ($node->rgt - $node->lft + 1))
			));

		// Calculate the offset between where the node used to be in the tree and
		// where it needs to be in the tree for left ids (also works for right ids).
		$offset = $repositionData->new_lft - $node->lft;
		$levelOffset = $repositionData->new_level - $node->level;

		// Move the nodes back into position in the tree using the calculated offsets.
		self::query()
			->where('lft', '<', 0)
			->update(array(
				'rgt'   => DB::raw((int) $offset . ' - rgt'),
				'lft'   => DB::raw((int) $offset . ' - lft'),
				'level' => DB::raw('level + ' . (int) $levelOffset)
			));

		// Set the correct parent id for the moved node if required.
		if ($node->parent_id != $repositionData->new_parent_id)
		{
			self::query()
				->where('id', '=', (int) $node->id)
				->update(array(
					'parent_id' => (int) $repositionData->new_parent_id
				));
		}

		// Set the object values.
		$this->parent_id = $repositionData->new_parent_id;
		$this->level = $repositionData->new_level;
		$this->lft = $repositionData->new_lft;
		$this->rgt = $repositionData->new_rgt;

		return true;
	}

	/**
	 * Get positive rating
	 *
	 * @return  int
	 */
	public function getPositiveRatingAttribute(): int
	{
		$total = $this->feedback()
			->count();

		if (!$total)
		{
			return 0;
		}

		$positive = $this->feedback()
			->where('type', '=', 'positive')
			->count();

		return ($positive / $total) * 100;
	}

	/**
	 * Get negative rating
	 *
	 * @return  int
	 */
	public function getNegativeRatingAttribute(): int
	{
		$total = $this->feedback()
			->count();

		if (!$total)
		{
			return 0;
		}

		$negative = $this->feedback()
			->where('type', '=', 'negative')
			->count();

		return ($negative / $total) * 100;
	}

	/**
	 * Get neutral rating
	 *
	 * @return  int
	 */
	public function getNeutralRatingAttribute(): int
	{
		$total = $this->feedback()
			->count();

		if (!$total)
		{
			return 0;
		}

		$neutral = $this->feedback()
			->where('type', '=', 'neutral')
			->count();

		return ($neutral / $total) * 100;
	}
}
