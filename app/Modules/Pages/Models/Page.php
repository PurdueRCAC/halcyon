<?php
/**
 * @package   hubzero-cms
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Pages\Models;

use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Halcyon\Config\Registry;
use App\Modules\History\Traits\Historable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Pages\Events\PageCreating;
use App\Modules\Pages\Events\PageCreated;
use App\Modules\Pages\Events\PageUpdating;
use App\Modules\Pages\Events\PageUpdated;
use App\Modules\Pages\Events\PageDeleted;
use App\Modules\Pages\Events\PageContentIsRendering;
use App\Halcyon\Models\Casts\Params;
//use App\Modules\Pages\Events\PageContentBeforeDisplay;
//use App\Modules\Pages\Events\PageContentAfterDisplay;
//use App\Modules\Pages\Events\PageTitleAfterDisplay;

/**
 * Model class for a page
 */
class Page extends Model
{
	use ErrorBag, Validatable, Historable, SoftDeletes;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	//const CREATED_AT = 'created_at';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var  string
	 */
	//const UPDATED_AT = 'updated_at';

	/**
	 * The table to which the class pertains
	 *
	 * This will default to #__{namespace}_{modelName} unless otherwise
	 * overwritten by a given subclass. Definition of this property likely
	 * indicates some derivation from standard naming conventions.
	 *
	 * @var  string
	 **/
	protected $table = 'pages';

	/**
	 * The model's default values for attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'state' => 0,
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id',
		'params',
		//'metadata',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
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
		//'metadata' => Params::class,
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'alias' => 'required'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
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
	 * Registry
	 *
	 * @var  object
	 */
	//protected $paramsRegistry = null;

	/**
	 * Registry params object 
	 *
	 * @var  object
	 */
	//protected $metadataRegistry = null;

	/**
	 * Generates automatic alias field value
	 *
	 * @param   string  $value
	 * @return  void
	 */
	/*public function something()
	{
		self::saving(function ($page)
		{
			if (!$page->parent_id)
			{
				$page->lft = 1;
			}

			if (!$page->rgt)
			{
				if (!$page->lft)
				{
					$page->lft = $this->automaticLft();
				}
				$page->rgt = $page->lft + 1;
			}

			if ($page->id)
			{
				$page->updated = Carbon\Carbon::now()->toDateTimeString();
			}

			return $page->isValid();
		});
	}*/

	/**
	 * Validate data
	 *
	 * @param   array $data
	 * @return  bool
	 */
	/*public function isValid($data = array())
	{
		if (empty($data))
		{
			$data = $this->attributes;
		}

		$v = Validator::make($data, $this->rules);

		return $v->passes();
	}*/

	/**
	 * Does the page exist?
	 *
	 * @return  boolean
	 */
	public function exists()
	{
		return !!$this->id;
	}

	/**
	 * Generates automatic alias field value
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setAliasAttribute($value)
	{
		$alias = strip_tags($value);
		$alias = trim($alias);
		if (strlen($alias) > 100)
		{
			$alias = substr($alias . ' ', 0, 100);
			$alias = substr($alias, 0, strrpos($alias,' '));
		}
		$alias = str_replace(' ', '-', $alias);

		$this->attributes['alias'] = preg_replace("/[^a-zA-Z0-9\-\_]/", '', strtolower($alias));
	}

	/**
	 * Generates automatic lft value
	 *
	 * @param   array   $data  the data being saved
	 * @return  string
	 */
	/*public function setLftAttribute($lft)
	{
		if (!$this->getAttribute('parent_id'))
		{
			$lft = 0;
		}
		return $lft;
	}*/

	/**
	 * Generates automatic lft value
	 *
	 * @param   array   $data  the data being saved
	 * @return  string
	 */
	/*public function setRgtAttribute($rgt)
	{
		if (!isset($rgt))
		{
			if (!$this->hasAttribute('lft'))
			{
				$lft = 0;
				$lft = $this->setLftAttribute($lft);
			}
			$rgt = $lft + 1;
		}
		return $rgt;
	}*/

	/**
	 * Get a params Registry object
	 *
	 * @return  object
	 */
	/*public function getOptionsAttribute()
	{
		if (!($this->paramsRegistry instanceof Registry))
		{
			$this->paramsRegistry = new Registry($this->getOriginal('params'));
		}

		return $this->paramsRegistry;
	}*/

	/**
	 * Get path
	 *
	 * @return  object
	 */
	public function getPathAttribute($path)
	{
		return $path ? $path : '/';
	}

	/**
	 * Get path
	 *
	 * @return  object
	 */
	public function getStylesAttribute()
	{
		return (array)$this->params->get('styles', []);
	}

	/**
	 * Get path
	 *
	 * @return  object
	 */
	public function getScriptsAttribute()
	{
		return (array)$this->params->get('scripts', []);
	}

	/**
	 * Parses title string as directed
	 *
	 * @return  string
	 */
	/*public function getTitleAttribute()
	{
		return $this->current->title;
	}*/

	/**
	 * Parses content string as directed
	 *
	 * @return  string
	 */
	public function getBodyAttribute()
	{
		$content = $this->content;

		//event($event = new PageContentBeforeDisplay($content));
		//$content = $event->getBody();

		event($event = new PageContentIsRendering($content));
		$content = $event->getBody();

		//event($event = new PageContentAfterDisplay($content));
		//$content = $event->getBody();

		// simple performance check to determine whether bot should process further
		if (strpos($content, '@file') === false)
		{
			return $content;
		}

		// expression to search for
		$regex = "/@filesize\(([^\)]*)\)/i";

		// find all instances of plugin and put in $matches
		preg_match_all($regex, $content, $matches, PREG_SET_ORDER);

		if ($matches)
		{
			foreach ($matches as $match)
			{
				$path = strtolower(trim($match[1]));
				$path = trim($path, '"\'');

				$text = \storage_path('app/public/' . $path);

				if (is_file($text))
				{
					$text = \App\Halcyon\Utility\Number::formatBytes(filesize($text));
				}
				else
				{
					$text .= trans('pages::pages.file not found');
				}

				$content = str_replace($match[0], $text, $content);
			}
		}

		// expression to search for
		$regex = "/@file\(([^\)]*)\)/i";

		// find all instances of plugin and put in $matches
		preg_match_all($regex, $content, $matches, PREG_SET_ORDER);

		if ($matches)
		{
			foreach ($matches as $match)
			{
				$path = strtolower(trim($match[1]));
				$path = trim($path, '"\'');

				$text = \asset('storage/' . $path);

				$content = str_replace($match[0], $text, $content);
			}
		}

		return $content;
	}

	/**
	 * Get metadesc from current version
	 *
	 * @return  string
	 */
	/*public function getMetadescAttribute()
	{
		return $this->current->metadesc;
	}*/

	/**
	 * Get metakey from current version
	 *
	 * @return  string
	 */
	/*public function getMetakeyAttribute()
	{
		return $this->current->metakey;
	}*/

	/**
	 * Get metadata from current version
	 *
	 * @return  object
	 */
	/*public function getMetadataAttribute()
	{
		if (!($this->metadataRegistry instanceof Registry))
		{
			$this->metadataRegistry = new Registry($this->current->metadata);
		}

		return $this->metadataRegistry;
	}*/

	/**
	 * Retrieves one row loaded by an alias and parent_id fields
	 *
	 * @param   string   $alias
	 * @param   integer  $parent_id
	 * @return  object
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
	 * @return  object
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
	 * @param   integer  $id  Primary key of the node for which to get the path.
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
			//$root = self::rootNode();

			//$rows = new Rows;
			//$rows->push($root);

			return collect([self::rootNode()]); //$rows;
		}

		$model = new self();

		// Get the path from the node to the root.
		$results = self::withTrashed()
			->select('p.*')
			->from($model->getTable() . ' AS n')
			->join($model->getTable() . ' AS p', 'p.level', '>', DB::raw('0'))
			->whereRaw('n.lft BETWEEN p.lft AND p.rgt')
			->where('n.path', '=', (string) $path)
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
	 * @return  object
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
	 * @return  object
	 */
	public function asset()
	{
		return $this->belongsTo('App\Halcyon\Access\Asset', 'asset_id');
	}

	/**
	 * Get the creator of this entry
	 *
	 * @return  object
	 */
	public function creator()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'created_by')->withDefault(); //app('request')->user()->toArray()
	}

	/**
	 * Get the modifier of this entry
	 *
	 * @return  object
	 */
	public function modifier()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'updated_by')->withDefault();
	}

	/**
	 * Establish relationship to editor
	 *
	 * @return  object
	 */
	public function editor()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'checked_out')->withDefault();
	}

	/**
	 * Determine if record is the home page
	 * 
	 * @return  boolean
	 */
	public function isRoot()
	{
		return ($this->id && $this->level == 0);
	}

	/**
	 * Determine if record was updated
	 * 
	 * @return  boolean
	 */
	public function isModified()
	{
		return ($this->updated && $this->updated != '0000-00-00 00:00:00');
	}

	/**
	 * Determine if record was updated
	 * 
	 * @return  boolean
	 */
	/*public function isDeleted()
	{
		return ($this->state == 2);
	}*/

	/**
	 * Determine if record is published
	 * 
	 * @return  boolean
	 */
	public function isPublished()
	{
		if ($this->state != 1)
		{
			return false;
		}

		if ($this->publish_up
		 && $this->publish_up != '0000-00-00 00:00:00'
		 && $this->publish_up > Carbon::now()->toDateTimeString())
		{
			return false;
		}

		if ($this->publish_down
		 && $this->publish_down != '0000-00-00 00:00:00'
		 && $this->publish_down <= Carbon::now()->toDateTimeString())
		{
			return false;
		}

		return true;
	}

	/**
	 * Get the access level
	 *
	 * @return  object
	 */
	public function viewlevel()
	{
		return $this->hasOne('App\Halcyon\Access\Viewlevel', 'id', 'access');
	}

	/**
	 * Get parent
	 *
	 * @return  object
	 */
	public function parent()
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
	 * @return  object
	 */
	public function children()
	{
		return $this->hasMany(self::class, 'parent_id');
		/*return self::query()
			->where('parent_id', '=', (int) $this->id)
			->get();*/
	}

	/**
	 * Get revision
	 *
	 * @return  object
	 */
	/*public function current()
	{
		return $this->hasOne(Version::class, 'id', 'version_id')->withDefault();
	}*/

	/**
	 * Get revisions
	 *
	 * @return  object
	 */
	/*public function versions()
	{
		return $this->hasMany(Version::class, 'page_id');
	}*/

	/**
	 * Copy an entry and associated data
	 *
	 * @param   integer  $parent_id  New version to copy to
	 * @param   boolean  $recursive  Copy associated data?
	 * @return  boolean  True on success, false on error
	 */
	public function duplicate($parent_id=null, $recursive=true)
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

		// Create a version for the newly creatd page
		/*$version = new Version(array(
			'page_id'  => $this->id,
			'version'  => 1,
			'title'    => $old->title . ' (copy)',
			'content'  => $old->content,
			'metakey'  => $old->metakey,
			'metadesc' => $old->metadesc,
			'metadata' => $old->metadata,
			'length'   => $old->length,
		));

		if (!$version->save())
		{
			$this->addError($version->getError());
			return false;
		}

		$this->version_id = $version->id;

		if (!$this->save())
		{
			return false;
		}*/

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
					$this->addError($child->getError());
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Save the record
	 *
	 * @return  boolean  False if error, True on success
	 */
	public function save(array $options = array())
	{
		if (!$this->access)
		{
			$this->access = (int) config('access', 1);
		}

		/*$data = array();
		foreach (array('title', 'content', 'metakey', 'metadesc', 'metadata') as $key)
		{
			$data[$key] = '';

			if (array_key_exists($key, $this->attributes))
			{
				$data[$key] = (string)$this->attributes[$key];
				unset($this->attributes[$key]);
			}
		}*/

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
				$this->addError(trans('Parent node does not exist.'));
				return false;
			}

			// Get the reposition data for shifting the tree and re-inserting the node.
			if (!($reposition = $this->getTreeRepositionData($parent, 2, 'last-child')))
			{
				// Error message set in getNode method.
				return false;
			}

			// Shift left values.
			$query = DB::table($this->getTable())
				->where($reposition->left_where['col'], $reposition->left_where['op'], $reposition->left_where['val'])
				->update(['lft' => DB::raw('lft + 2')]);

			/*if (!$query)
			{
				$this->addError('Failed to update lft values');
				return false;
			}*/

			// Shift right values.
			$query = DB::table($this->getTable())
				->where($reposition->right_where['col'], $reposition->right_where['op'], $reposition->right_where['val'])
				->update(['rgt' => DB::raw('rgt + 2')]);

			/*if (!$query)
			{
				$this->addError('Failed to update rgt values');
				return false;
			}*/

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
			//var_dump($this->getOriginal('state')); die();
			//if ($this->wasChanged('state') && $this->state != 1)
			if ($this->getOriginal('state') != $this->state && $this->state != 1)
			{
				foreach ($this->children as $child)
				{
					if (!$child->update(['state' => $this->state]))
					{
						$this->addError($child->getError());
						return false;
					}
				}
			}

			$parent = $this->parent;

			$this->updated_by = auth()->user() ? auth()->user()->id : 0;
		}

		$this->level = $this->parent_id ? $parent->level + 1 : 0;

		$result = parent::save($options);

		// We don't want to mess with versions if doing
		// something simple like toggling state
		/*if ($result && !empty($data))
		{
			// Were any trackable fields changed?
			$update = $this->id ? false : true;

			if ($this->id)
			{
				foreach ($data as $key => $val)
				{
					if ($val != $this->current->{$key})
					{
						$update = true;
						break;
					}
				}
			}

			if ($update)
			{
				// Create a new version
				$version = new Version($data);
				$version->page_id = $this->id;

				if (!$version->save())
				{
					$this->addError($version->getError());
					return false;
				}

				// Update necessary info
				$this->version_id = $version->id;

				return parent::save($options);
			}
		}*/

		return $result;
	}

	/**
	 * Method to rebuild the node's path field from the alias values of the
	 * nodes from the current node to the root node of the tree.
	 *
	 * @return  boolean  True on success.
	 */
	public function rebuildPath()
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
			$this->addError(trans('global.ERROR_REBUILDPATH_FAILED', get_class($this)));
			return false;
		}

		// Update the current record's path to the new one:
		$this->path = $path;

		return true;
	}

	/**
	 * Method to recursively rebuild the whole nested set tree.
	 *
	 * @param   integer  $parentId  The root of the tree to rebuild.
	 * @param   integer  $leftId    The left id to start with in building the tree.
	 * @param   integer  $level     The level to assign to the current nodes.
	 * @param   string   $path      The path to the current nodes.
	 * @return  integer  1 + value of root rgt on success, false on failure
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
		//var_dump($query);
		//echo $path . ', ' . $leftId . ',' . $rightId . '<br />';
		// If there is an update failure, return false to break out of the recursion.
		/*if (!$query)
		{
			return false;
		}*/

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
	 * @param   integer  $nodeWidth      The width of the node for which to make room in the tree.
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
	public function toTree($rows)
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
	 * Delete the record and all associated data
	 *
	 * @return  boolean  False if error, True on success
	 */
	public function delete(array $options = [])
	{
		foreach ($this->children as $row)
		{
			if (!$row->delete($options))
			{
				$this->addError($row->getError());
				return false;
			}
		}

		// Attempt to delete the record
		return parent::delete($options);
	}

	/**
	 * Recursive function to build tree
	 *
	 * @param   array    $children  Container for parent/children mapping
	 * @param   array    $list      List of records
	 * @param   integer  $maxlevel  Maximum levels to descend
	 * @param   integer  $level     Indention level
	 * @return  void
	 */
	protected function treeRecurse($children, $list, $maxlevel=9999, $level=0)
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
	 * @param   integer  $referenceId  The primary key of the node to reference new location by.
	 * @param   string   $position     Location type string. ['before', 'after', 'first-child', 'last-child']
	 * @return  boolean  True on success.
	 */
	/*public function setLocation($referenceId, $position = 'after')
	{
		// Make sure the location is valid.
		if (!in_array($position, array('before', 'after', 'first-child', 'last-child')))
		{
			$this->addError(trans('JLIB_DATABASE_ERROR_INVALID_LOCATION', get_class($this)));
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
	 * @param   integer  $delta  The direction and magnitude to move the row in the ordering sequence.
	 * @param   string   $where  WHERE clause to use for limiting the selection of rows to compact the ordering values.
	 * @return  mixed    Boolean true on success.
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

		$this->addError(trans('Database error: Move failed') . ': Reference not found for delta ' . $delta);

		return false;
	}

	/**
	 * Method to move a node and its children to a new location in the tree.
	 *
	 * @param   integer  $referenceId  The primary key of the node to reference new location by.
	 * @param   string   $position     Location type string. ['before', 'after', 'first-child', 'last-child']
	 * @param   integer  $pk           The primary key of the node to move.
	 * @return  boolean  True on success.
	 */
	public function moveByReference($referenceId, $position = 'after', $pk = 0)
	{
		// Initialise variables.
		//$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->id : $pk;

		// Get the node by id.
		$node = self::oneOrNew($pk);

		if (!$node->id)
		{
			// Error message set in getNode method.
			$this->addError(trans('Database error: Move failed') . ': Node not found #' . $pk);
			return false;
		}

		// Get the ids of child nodes.
		$children = self::query()
			->select('id')
			->whereRaw('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt)
			->pluck('id');

		// Cannot move the node to be a child of itself.
		if (in_array($referenceId, $children))
		{
			$this->addError(trans('Database error: Invalid node recursion in :class', ['class' => get_class($this)]));
			return false;
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
			$this->addError(trans('Database error: Move failed'));
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
			$this->addError(trans('Database error: Move failed'));
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
			$this->addError(trans('Database error: Move failed'));
			return false;
		}

		// We are moving the tree relative to a reference node.
		if ($referenceId)
		{
			// Get the reference node by primary key.
			$reference = self::oneOrNew($referenceId);

			if (!$reference->id)
			{
				$this->addError(trans('Database error: Move failed') . ': Reference not found #' . $referenceId);
				return false;
			}

			// Get the reposition data for shifting the tree and re-inserting the node.
			if (!$repositionData = $this->getTreeRepositionData($reference, ($node->rgt - $node->lft + 1), $position))
			{
				$this->addError(trans('Database error: Move failed') . ': Reposition data');
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
				$this->addError(trans('Database error: Move failed') . ': Reposition data');
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
			$this->addError(trans('Database error: Move failed'));
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
			$this->addError(trans('Database error: Move failed'));
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
			$this->addError(trans('Database error: Move failed'));
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
				$this->addError(trans('Database error: Move failed'));
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
}
