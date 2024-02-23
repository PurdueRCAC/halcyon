<?php

namespace App\Modules\Pages\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use App\Modules\Pages\Events\PageCreating;
use App\Modules\Pages\Events\PageCreated;
use App\Modules\Pages\Events\PageUpdating;
use App\Modules\Pages\Events\PageUpdated;
use App\Modules\Pages\Events\PageDeleted;
use App\Modules\Pages\Events\PageContentIsRendering;
use App\Modules\Pages\Events\PageMetadata;
use App\Modules\Pages\Formatters\FilePath;
use App\Modules\Pages\Formatters\FileSize;
use App\Modules\Pages\Formatters\IncludeSvg;
use App\Modules\History\Traits\Historable;
use App\Modules\Tags\Traits\Taggable;
use App\Halcyon\Models\Casts\Params;
use Carbon\Carbon;

/**
 * Model class for a page
 *
 * @property int    $id
 * @property string $title
 * @property string $alias
 * @property int    $state
 * @property int    $access
 * @property Carbon|null $created_at
 * @property int    $created_by
 * @property Carbon|null $updated_at
 * @property int    $updated_by
 * @property Carbon|null $deleted_at
 * @property int    $checked_out
 * @property Carbon|null $checked_out_time
 * @property Carbon|null $publish_up
 * @property Carbon|null $publish_down
 * @property int    $parent_id
 * @property int    $hits
 * @property int    $left
 * @property int    $rgt
 * @property int    $level
 * @property string $path
 * @property string $language
 * @property int    $asset_id
 * @property int    $version_id
 * @property string $params
 * @property string $content
 * @property string $metakey
 * @property string $metadesc
 * @property string $metadata
 */
class Page extends Model
{
	use Historable, SoftDeletes, Taggable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'pages';

	/**
	 * The model's default values for attributes.
	 *
	 * @var array<string,int>
	 */
	protected $attributes = [
		'state' => 0,
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id',
		'params',
		//'metadata',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array<string,string>
	 */
	protected $casts = [
		'state' => 'integer',
		'access' => 'integer',
		'created_by' => 'integer',
		'updated_by' => 'integer',
		'parent_id' => 'integer',
		'hits' => 'integer',
		'lft' => 'integer',
		'rgt' => 'integer',
		'level' => 'integer',
		'asset_id' => 'integer',
		'version_id' => 'integer',
		'params' => Params::class,
		'metadata' => Params::class,
	];

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'creating' => PageCreating::class,
		'created'  => PageCreated::class,
		'updating' => PageUpdating::class,
		'updated'  => PageUpdated::class,
		'deleted'  => PageDeleted::class,
		//'restored' => PageRestored::class,
	];

	/**
	 * Cached content has been parsed
	 *
	 * @var string|null
	 */
	private $parsedContent = null;

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
	 * Does the page exist?
	 *
	 * @return  bool
	 */
	public function exists(): bool
	{
		return !!$this->id;
	}

	/**
	 * Boot
	 *
	 * @return  void
	 */
	public static function boot(): void
	{
		parent::boot();

		self::deleted(function($model)
		{
			foreach ($model->children as $row)
			{
				$row->delete();
			}
		});
	}

	/**
	 * Generates automatic alias field value
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setAliasAttribute($value): void
	{
		$alias = strip_tags($value);
		$alias = trim($alias);
		if (strlen($alias) > 100)
		{
			$alias = substr($alias . ' ', 0, 100);
			$alias = substr($alias, 0, strrpos($alias,' '));
		}
		$alias = str_replace(' ', '-', $alias);

		$this->attributes['alias'] = preg_replace("/[^a-zA-Z0-9\-\_\.]/", '', $alias);
	}

	/**
	 * Get path
	 *
	 * @param   string  $path
	 * @return  string
	 */
	public function getPathAttribute($path): string
	{
		return $path ? $path : '/';
	}

	/**
	 * Get list of styles
	 *
	 * @return  array<int,string>
	 */
	public function getStylesAttribute(): array
	{
		return array_filter((array)$this->params->get('styles', []));
	}

	/**
	 * Get list of scripts
	 *
	 * @return  array<int,string>
	 */
	public function getScriptsAttribute(): array
	{
		return array_filter((array)$this->params->get('scripts', []));
	}

	/**
	 * Gather page metadata
	 *
	 * @return  void
	 */
	public function gatherMetadata(): void
	{
		event(new PageMetadata($this));
	}

	/**
	 * Parses content string as directed
	 *
	 * @return  string
	 */
	public function getBodyAttribute(): string
	{
		if (is_null($this->parsedContent))
		{
			$content = $this->content;

			event($event = new PageContentIsRendering($content));
			$content = $event->getBody();

			$content = app(Pipeline::class)
					->send($content)
					->through([
						FileSize::class,
						FilePath::class,
						IncludeSvg::class,
					])
					->thenReturn();

			$this->parsedContent = $content ? $content : '';
		}

		return $this->parsedContent;
	}

	/**
	 * Retrieves one row loaded by an alias and parent_id fields
	 *
	 * @param   string   $alias
	 * @param   int  $parent_id
	 * @return  Page|null
	 */
	public static function findByAlias($alias, $parent_id=0)
	{
		return self::query()
			->where('alias', '=', (string)$alias)
			->where('parent_id', '=', (int)$parent_id)
			->limit(1)
			->first();
	}

	/**
	 * Retrieves one row loaded by path
	 *
	 * @param   string  $path
	 * @return  Page|null
	 */
	public static function findByPath($path)
	{
		return self::query()
			->where('path', '=', (string)$path)
			->limit(1)
			->first();
	}

	/**
	 * Method to get a list of nodes from a given node to its root.
	 *
	 * @param   int  $id  Primary key of the node for which to get the path.
	 * @return  mixed    Boolean false on failure or array of node objects on success.
	 */
	public static function stackById($id)
	{
		$model = self::query();

		// Get the path from the node to the root.
		$results = $model
			->select('p.*')
			->from($model->getTable() . ' AS n')
			->join($model->getTable() . ' AS p', 'p.level', '>', '0')
			->whereRaw('n.lft BETWEEN p.lft AND p.rgt')
			->where('n.id', '=', (int) $id)
			->orderBy('p.lft', 'asc')
			->get();

		if (!$results)
		{
			return false;
		}

		return $results;
	}

	/**
	 * Method to get a list of nodes from a given node to its root.
	 *
	 * @param   string  $path  Primary key of the node for which to get the path.
	 * @return  mixed   Boolean false on failure or array of node objects on success.
	 */
	public static function stackByPath($path)
	{
		if (!$path)
		{
			return collect([self::rootNode()]);
		}

		$model = new self();

		// Get the path from the node to the root.
		$results = self::withTrashed()
			->select('p.*')
			->from($model->getTable() . ' AS n')
			->join($model->getTable() . ' AS p', 'p.level', '>', DB::raw('0'))
			->whereRaw('n.lft BETWEEN p.lft AND p.rgt')
			->where('n.path', '=', (string) $path)
			->whereNull('p.deleted_at')
			->orderBy('p.lft', 'asc')
			->get();

		if (!$results)
		{
			return array();
		}

		return $results;
	}

	/**
	 * Get the root node
	 *
	 * @return  Page|null
	 */
	public static function rootNode()
	{
		return self::query()
			->where('level', '=', 0)
			->orderBy('lft', 'asc')
			->orderBy('id', 'asc')
			->limit(1)
			->first();
	}

	/**
	 * Establish relationship to asset
	 *
	 * @return  BelongsTo
	 */
	public function asset(): BelongsTo
	{
		return $this->belongsTo('App\Halcyon\Access\Asset', 'asset_id');
	}

	/**
	 * Get the creator of this entry
	 *
	 * @return  BelongsTo
	 */
	public function creator(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'created_by')->withDefault();
	}

	/**
	 * Get the modifier of this entry
	 *
	 * @return  BelongsTo
	 */
	public function modifier(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'updated_by')->withDefault();
	}

	/**
	 * Establish relationship to editor
	 *
	 * @return  BelongsTo
	 */
	public function editor(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'checked_out')->withDefault();
	}

	/**
	 * Establish relationship to log model
	 *
	 * @return  object
	 */
	public function logs()
	{
		return \App\Modules\History\Models\Log::query()
			->where('app', '=', 'ui')
			->where('transportmethod', '=', 'GET')
			->where('uri', '=', '/' . $this->path);
	}

	/**
	 * Determine if record is the home page
	 * 
	 * @return  bool
	 */
	public function isRoot(): bool
	{
		return ($this->id && $this->level == 0);
	}

	/**
	 * Determine if record was updated
	 * 
	 * @return  bool
	 */
	public function isModified(): bool
	{
		return $this->updated_at;
	}

	/**
	 * Determine if record is published
	 * 
	 * @return  bool
	 */
	public function isPublished(): bool
	{
		if ($this->state != 1)
		{
			return false;
		}

		if ($this->publish_up
		 && $this->publish_up > Carbon::now()->toDateTimeString())
		{
			return false;
		}

		if ($this->publish_down
		 && $this->publish_down <= Carbon::now()->toDateTimeString())
		{
			return false;
		}

		return true;
	}

	/**
	 * Get the access level
	 *
	 * @return  HasOne
	 */
	public function viewlevel(): HasOne
	{
		return $this->hasOne('App\Halcyon\Access\Viewlevel', 'id', 'access');
	}

	/**
	 * Get parent
	 *
	 * @return  BelongsTo
	 */
	public function parent(): BelongsTo
	{
		return $this->belongsTo(self::class, 'parent_id')->withDefault();
	}

	/**
	 * Get all aprents
	 *
	 * @return  array
	 */
	public function ancestors()
	{
		$page = $this->parent;

		$ancestors = array();

		if ($page->id && !($page->alias == 'home' && $page->level == 0))
		{
			$ancestors[] = $page;

			if ($page->parent_id)
			{
				foreach ($page->ancestors() as $ancestor)
				{
					array_unshift($ancestors, $ancestor);
				}
			}
		}

		return collect($ancestors);
	}

	/**
	 * Get child entries
	 *
	 * @return  HasMany
	 */
	public function children(): HasMany
	{
		return $this->hasMany(self::class, 'parent_id');
	}

	/**
	 * Copy an entry and associated data
	 *
	 * @param   int   $parent_id  New version to copy to
	 * @param   bool  $recursive  Copy associated data?
	 * @return  bool  True on success, false on error
	 */
	public function duplicate($parent_id=null, $recursive=true): bool
	{
		// Get some old info we may need
		$o_id = $this->id;
		$c_id = $this->parent_id;

		// Reset the ID. This will force save() to create a new record.
		$this->id = 0;

		// Are we copying to the same parent?
		if ($parent_id == $this->parent_id)
		{
			// Copying to the same parent so we want to distinguish
			// this from the one we copied from
			$this->alias .= '_copy';
			$this->title .= ' (copy)';
		}

		$old = $this->current;

		$this->parent_id = $parent_id;

		if (!$this->save())
		{
			return false;
		}

		if ($recursive)
		{
			// Copy children
			$children = self::query()
				->where('parent_id', '=', $o_id)
				->get();

			foreach ($children as $child)
			{
				if (!$child->duplicate($this->id, $recursive))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Save the record
	 *
	 * @param   array<string,mixed>  $options
	 * @return  bool  False if error, True on success
	 */
	public function save(array $options = array()): bool
	{
		if (!$this->access)
		{
			$this->access = (int) config('access', 1);
		}

		$isNew = !$this->exists();

		if ($isNew)
		{
			if (!$this->parent_id)
			{
				$root = self::rootNode();

				$this->lft = $root->lft + 1;
				$this->rgt = $root->lft + 2;
				$this->parent_id = $root->id;
			}

			$parent = $this->parent;

			if (!$parent || !$parent->id)
			{
				throw new \Exception(trans('Parent node does not exist.'));
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

			foreach ($this->ancestors() as $ancestor)
			{
				$path[] = $ancestor->alias;
			}

			$path[] = $this->alias;

			$this->path  = implode('/', $path);
			$this->lft   = $reposition->new_lft;
			$this->rgt   = $reposition->new_rgt;

			if (!$this->created_by)
			{
				$this->created_by = auth()->user() ? auth()->user()->id : 0;
			}
		}
		else
		{
			// If unpublishing or trashing, cascade to children
			if ($this->getOriginal('state') != $this->state && $this->state != 1)
			{
				foreach ($this->children as $child)
				{
					if (!$child->update(['state' => $this->state]))
					{
						return false;
					}
				}
			}

			$parent = $this->parent;

			$this->updated_by = auth()->user() ? auth()->user()->id : 0;
		}

		$this->level = $this->parent_id ? $parent->level + 1 : 0;

		return parent::save($options);
	}

	/**
	 * Method to rebuild the node's path field from the alias values of the
	 * nodes from the current node to the root node of the tree.
	 *
	 * @return  bool  True on success.
	 */
	public function rebuildPath(): bool
	{
		// Get the aliases for the path from the node to the root node.
		$path = $this->parent->path;
		$segments = explode('/', $path);

		// Make sure to remove the root path if it exists in the list.
		if ($segments[0] == 'root')
		{
			array_shift($segments);
		}
		$segments[] = $this->alias;

		// Build the path.
		$path = trim(implode('/', $segments), ' /\\');

		// Update the path field for the node.
		$query = DB::table($this->getTable())
			->where('id', '=', (int) $this->id)
			->update(array(
				'path' => $path
			));

		// Check for a database error.
		if (!$query)
		{
			//throw new \Exception(trans('pages::pages.error.path rebuild failed', get_class($this)));
			return false;
		}

		// Update the current record's path to the new one:
		$this->path = $path;

		return true;
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
	public function rebuild($parentId, $leftId = 0, $level = 0, $path = '')
	{
		// Assemble the query to find all children of this node.
		$children = self::query()
			->select('id', 'alias')
			->where('parent_id', '=', (int) $parentId)
			->orderBy('parent_id', 'asc')
			->orderBy('lft', 'asc')
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
				return false;
			}
		}

		// We've got the left value, and now that we've processed
		// the children of this node we also know the right value.
		$query = DB::table($this->getTable())
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
	 * @return  mixed    Boolean false on failure or data object on success.
	 */
	protected function getTreeRepositionData($referenceNode, $nodeWidth, $position = 'before')
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
		$k = $this->pk;

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
	 * Turn a list of rows into a tree
	 *
	 * @param   object  $rows
	 * @return  array
	 */
	public function toTree($rows): array
	{
		$results = array();

		if (count($rows) > 0)
		{
			$children = array();
			$children[$this->id] = array();

			foreach ($rows as $row)
			{
				$pt   = $row->parent_id;
				$list = @$children[$pt] ? $children[$pt] : array();

				array_push($list, $row);

				$children[$pt] = $list;
			}

			$results = $this->treeRecurse($children[$this->id], $children);
		}

		return $results;
	}

	/**
	 * Recursive function to build tree
	 *
	 * @param   array    $children  Container for parent/children mapping
	 * @param   array    $list      List of records
	 * @param   int  $maxlevel  Maximum levels to descend
	 * @param   int  $level     Indention level
	 * @return  void
	 */
	protected function treeRecurse($children, $list, $maxlevel=9999, $level=0): array
	{
		if ($level <= $maxlevel)
		{
			foreach ($children as $v => $child)
			{
				$replies = array();

				if (isset($list[$child->id]))
				{
					$replies = $this->treeRecurse($list[$child->id], $list, $maxlevel, $level+1);
				}

				$children[$v]->children = $replies;
			}
		}
		return $children;
	}

	/**
	 * Method to set the location of a node in the tree object.  This method does not
	 * save the new location to the database, but will set it in the object so
	 * that when the node is stored it will be stored in the new location.
	 *
	 * @param   int  $referenceId  The primary key of the node to reference new location by.
	 * @param   string   $position     Location type string. ['before', 'after', 'first-child', 'last-child']
	 * @return  bool     True on success.
	 */
	/*public function setLocation($referenceId, $position = 'after')
	{
		// Make sure the location is valid.
		if (!in_array($position, array('before', 'after', 'first-child', 'last-child')))
		{
			throw new \Exception(trans('core::core.error.invalid location', get_class($this)));
			return false;
		}

		// Set the location properties.
		$this->_location    = $position;
		$this->_location_id = $referenceId;

		return true;
	}*/

	/**
	 * Method to move a row in the ordering sequence of a group of rows defined by an SQL WHERE clause.
	 * Negative numbers move the row up in the sequence and positive numbers move it down.
	 *
	 * @param   int  $delta  The direction and magnitude to move the row in the ordering sequence.
	 * @param   string   $where  WHERE clause to use for limiting the selection of rows to compact the ordering values.
	 * @return  mixed    Boolean true on success.
	 * @throws  \Exception
	 */
	public function move($delta, $where = '')
	{
		$query = self::query()
			->select('id')
			->where('parent_id', '=', $this->parent_id);
		if ($where)
		{
			$query->whereRaw($where);
		}

		$position = 'after';

		if ($delta > 0)
		{
			$query->where('rgt', '>', $this->rgt);
			$query->orderBy('rgt', 'ASC');
			$position = 'after';
		}
		else
		{
			$query->where('lft', '<', $this->lft);
			$query->orderBy('lft', 'DESC');
			$position = 'before';
		}

		$referenceId = $query->value('id');

		if ($referenceId)
		{
			return $this->moveByReference($referenceId, $position, $this->id);
		}

		throw new \Exception(trans('Database error: Move failed') . ': Reference not found for delta ' . $delta);
	}

	/**
	 * Method to move a node and its children to a new location in the tree.
	 *
	 * @param   int  $referenceId  The primary key of the node to reference new location by.
	 * @param   string   $position     Location type string. ['before', 'after', 'first-child', 'last-child']
	 * @param   int  $pk           The primary key of the node to move.
	 * @return  bool     True on success.
	 * @throws  \Exception
	 */
	public function moveByReference($referenceId, $position = 'after', $pk = 0): bool
	{
		// Initialise variables.
		//$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->id : $pk;

		// Get the node by id.
		$node = self::oneOrNew($pk);

		if (!$node->id)
		{
			// Error message set in getNode method.
			throw new \Exception(trans('Database error: Move failed') . ': Node not found #' . $pk);
		}

		// Get the ids of child nodes.
		$children = self::query()
			->select('id')
			->whereRaw('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt)
			->pluck('id');

		// Cannot move the node to be a child of itself.
		if (in_array($referenceId, $children))
		{
			throw new \Exception(trans('Database error: Invalid node recursion in :class', ['class' => get_class($this)]));
		}

		// Move the sub-tree out of the nested sets by negating its left and right values.
		$query = self::query()
			->whereRaw('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt)
			->update(array(
				'lft' => DB::raw('lft * (-1)'),
				'rgt' => DB::raw('rgt * (-1)')
			));

		if (!$query)
		{
			return false;
		}

		// Close the hole in the tree that was opened by removing the sub-tree from the nested sets.

		// Compress the left values.
		$query = self::query()
			->where('lft', '>', (int) $node->rgt)
			->update(array(
				'lft' => DB::raw('lft - ' . (int) ($node->rgt - $node->lft + 1))
			));

		if (!$query)
		{
			return false;
		}

		// Compress the right values.
		$query = self::query()
			->where('rgt', '>', (int) $node->rgt)
			->update(array(
				'rgt' => DB::raw('rgt - ' . (int) ($node->rgt - $node->lft + 1))
			));

		if (!$query)
		{
			return false;
		}

		// We are moving the tree relative to a reference node.
		if ($referenceId)
		{
			// Get the reference node by primary key.
			$reference = self::oneOrNew($referenceId);

			if (!$reference->id)
			{
				throw new \Exception(trans('Database error: Move failed') . ': Reference not found #' . $referenceId);
			}

			// Get the reposition data for shifting the tree and re-inserting the node.
			if (!$repositionData = $this->getTreeRepositionData($reference, ($node->rgt - $node->lft + 1), $position))
			{
				//throw new \Exception(trans('Database error: Move failed') . ': Reposition data');
				return false;
			}
		}
		// We are moving the tree to be the last child of the root node
		else
		{
			// Get the last root node as the reference node.
			$reference = self::query()
				->select('id', 'parent_id', 'level', 'lft', 'rgt')
				->where('parent_id', '=', 0)
				->orderBy('lft', 'desc')
				->limit(1)
				->first();

			// Get the reposition data for re-inserting the node after the found root.
			if (!$repositionData = $this->getTreeRepositionData($reference, ($node->rgt - $node->lft + 1), 'last-child'))
			{
				//throw new \Exception(trans('Database error: Move failed') . ': Reposition data');
				return false;
			}
		}

		// Create space in the nested sets at the new location for the moved sub-tree.

		// Shift left values.
		$query = DB::table($this->getTable())
			->where($repositionData->left_where['col'], $repositionData->left_where['op'], $repositionData->left_where['val'])
			->update(array(
				'lft' => DB::raw('lft + ' . (int) ($node->rgt - $node->lft + 1))
			));

		if (!$query)
		{
			return false;
		}

		// Shift right values.
		$query = DB::table($this->getTable())
			->where($repositionData->right_where['col'], $repositionData->right_where['op'], $repositionData->right_where['val'])
			->update(array(
				'rgt' => DB::raw('rgt + ' . (int) ($node->rgt - $node->lft + 1))
			));

		if (!$query)
		{
			return false;
		}

		// Calculate the offset between where the node used to be in the tree and
		// where it needs to be in the tree for left ids (also works for right ids).
		$offset      = $repositionData->new_lft - $node->lft;
		$levelOffset = $repositionData->new_level - $node->level;

		// Move the nodes back into position in the tree using the calculated offsets.
		$query = DB::table($this->getTable())
			->where('lft', '<', 0)
			->update(array(
				'rgt'   => DB::raw((int) $offset . ' - rgt'),
				'lft'   => DB::raw((int) $offset . ' - lft'),
				'level' => DB::raw('level + ' . (int) $levelOffset)
			));

		if (!$query)
		{
			return false;
		}

		// Set the correct parent id for the moved node if required.
		if ($node->parent_id != $repositionData->new_parent_id)
		{
			$query = DB::table($this->getTable())
				->where('id', '=', (int) $node->id)
				->update(array(
					'parent_id' => (int) $repositionData->new_parent_id
				));

			if (!$query)
			{
				return false;
			}
		}

		// Set the object values.
		$this->parent_id = $repositionData->new_parent_id;
		$this->level     = $repositionData->new_level;
		$this->lft       = $repositionData->new_lft;
		$this->rgt       = $repositionData->new_rgt;

		return true;
	}

	/**
	 * Query scope with search
	 *
	 * @param   Builder  $query
	 * @param   string   $search
	 * @return  Builder
	 */
	public function scopeWhereSearch(Builder $query, $search): Builder
	{
		$page = $this->getTable();

		$query->select(
			$page . '.*',
			DB::raw('(
					IF(' . $page . '.title = "' . $search . '", 30,
						IF(' . $page . '.title LIKE "' . $search . '%", 20,
							IF(' . $page . '.title LIKE "%' . $search . '%", 10, 0)
						)
					)
					+ IF(' . $page . '.content LIKE "%' . $search . '%", 5, 0)
					+ IF(' . $page . '.path    LIKE "%' . $search . '%", 1, 0)
				) * 2
				AS `weight`')
			)
			->orderBy('weight', 'desc');
		$query->where(function($query) use ($search, $page)
		{
			$query->where($page . '.title', 'like', $search . '%')
				->orWhere($page . '.title', 'like', '%' . $search . '%')
				->orWhere($page . '.content', 'like', '%' . $search . '%')
				->orWhere($page . '.path', 'like', '%' . $search . '%');
		});

		return $query;
	}

	/**
	 * Query scope with parent ID
	 *
	 * @param   Builder  $query
	 * @param   int  $parent_id
	 * @return  Builder
	 */
	public function scopeWhereParent(Builder $query, $parent_id): Builder
	{
		$page = $this->getTable();

		$parent = Page::findOrFail($parent_id);

		$query
			->where($page . '.lft', '>', $parent->lft)
			->where($page . '.rgt', '<', $parent->rgt);

		return $query;
	}

	/**
	 * Query scope with access
	 *
	 * @param   Builder  $query
	 * @param   int  $access
	 * @param   mixed  $user
	 * @return  Builder
	 */
	public function scopeWhereAccess(Builder $query, $access, $user): Builder
	{
		$levels = $user
			? $user->getAuthorisedViewLevels()
			: array(1);

		if (!$access)
		{
			$query->whereIn($this->getTable() . '.access', $levels);
		}
		else
		{
			if (!in_array($access, $levels))
			{
				$access = 1;
			}

			$query->where($this->getTable() . '.access', '=', (int)$access);
		}

		return $query;
	}

	/**
	 * Query scope with state
	 *
	 * @param   Builder  $query
	 * @param   mixed  $state
	 * @return  Builder
	 */
	public function scopeWhereState(Builder $query, $state): Builder
	{
		$page = $this->getTable();

		switch ($state)
		{
			case '*':
			case 'all':
				$query->withTrashed();
			break;

			case 'trashed':
			case 2:
				$query->onlyTrashed();
			break;

			case 'unpublished':
			case 0:
				$query->where($page . '.state', '=', 0);
			break;

			case 'published':
			case 1:
			default:
				$query->where($page . '.state', '=', 1);
		}

		return $query;
	}
}
