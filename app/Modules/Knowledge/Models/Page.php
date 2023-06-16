<?php

namespace App\Modules\Knowledge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Config\Repository;
use Illuminate\Pipeline\Pipeline;
use App\Modules\History\Traits\Historable;
use App\Modules\Knowledge\Events\PageContentIsRendering;
use App\Modules\Knowledge\Events\PageCreating;
use App\Modules\Knowledge\Events\PageCreated;
use App\Modules\Knowledge\Events\PageUpdating;
use App\Modules\Knowledge\Events\PageUpdated;
use App\Modules\Knowledge\Events\PageDeleted;
use App\Modules\Knowledge\Formatters\ReplaceVariables;
use App\Modules\Knowledge\Formatters\ControlStatements;
use App\Modules\Knowledge\Formatters\ReplaceIfStatements;
use App\Modules\Knowledge\Formatters\AdjustHeaderLevels;
use App\Modules\Knowledge\Formatters\AbsoluteFilePaths;
use App\Modules\Knowledge\Formatters\PermalinkHeaders;
use App\Modules\Tags\Traits\Taggable;
use App\Halcyon\Models\Casts\Params;
use Carbon\Carbon;

/**
 * Model class for a page
 *
 * @property int    $id
 * @property string $title
 * @property string $alias
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int    $state
 * @property int    $access
 * @property string $content
 * @property string $params
 * @property int    $main
 * @property int    $snippet
 */
class Page extends Model
{
	use Historable, SoftDeletes, Taggable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'kb_pages';

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
		'id'
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array<string,string>
	 */
	protected $casts = [
		'state'   => 'integer',
		'access'  => 'integer',
		'main'    => 'integer',
		'snippet' => 'integer',
		'params'  => Params::class,
	];

	/**
	 * The model's default values for attributes.
	 *
	 * @var array<int,string>
	 */
	protected $appends = [
		'headline',
		'body',
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
	];

	/**
	 * Cached attribute values
	 *
	 * @var array<string,mixed>
	 */
	protected $cachedAttributes = [];

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
	 * Page variables
	 *
	 * @var  Collection
	 */
	protected $varsRepository = null;

	/**
	 * Page metadata
	 *
	 * @var  Repository
	 */
	protected $metadataRepository = null;

	/**
	 * Does the Doc exist?
	 *
	 * @return  bool
	 */
	public function exists()
	{
		return !!$this->id;
	}

	/**
	 * Is this a separator
	 *
	 * @return  bool
	 */
	public function isSeparator(): bool
	{
		return ($this->alias == '-separator-');
	}

	/**
	 * Generates automatic alias field value
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setAliasAttribute(string $value): void
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
	 * Get a variables Repository object
	 *
	 * @return  Collection
	 */
	public function getVariablesAttribute(): Collection
	{
		if (!($this->varsRepository instanceof Collection))
		{
			$this->varsRepository = new Collection($this->getVars());
		}

		return $this->varsRepository;
	}

	/**
	 * Get a metadata Repository object
	 *
	 * @return  Repository
	 */
	public function getMetadataAttribute(): Repository
	{
		if (!($this->metadataRepository instanceof Repository))
		{
			$this->metadataRepository = new Repository();
		}

		return $this->metadataRepository;
	}

	/**
	 * Get title with variables replaced
	 *
	 * @return  string
	 */
	public function getHeadlineAttribute(): string
	{
		if (!isset($this->cachedAttributes['headline']))
		{
			$data = app(Pipeline::class)
					->send([
						'id' => $this->id,
						'content' => $this->title,
						'headline' => $this->title,
						'variables' => $this->variables->toArray(),
					])
					->through([
						ReplaceVariables::class,
					])
					->thenReturn();

			$this->cachedAttributes['headline'] = $data['content'];
		}

		return $this->cachedAttributes['headline'];
	}

	/**
	 * Get content with variables replaced
	 *
	 * @return  string
	 */
	public function getBodyAttribute(): string
	{
		if (!isset($this->cachedAttributes['body']))
		{
			$text = $this->content;

			event($event = new PageContentIsRendering($text));
			$text = $event->getBody();

			$data = app(Pipeline::class)
					->send([
						'id' => $this->id,
						'content' => $text,
						'headline' => $this->headline,
						'variables' => $this->variables->toArray(),
					])
					->through([
						ReplaceVariables::class,
						ReplaceIfStatements::class,
						AdjustHeaderLevels::class,
						AbsoluteFilePaths::class,
						PermalinkHeaders::class,
					])
					->thenReturn();

			$this->cachedAttributes['body'] = $data['content'];
		}

		return $this->cachedAttributes['body'];
	}

	/**
	 * Get variables for replacement
	 *
	 * @return  array<string,mixed>
	 */
	protected function getVars(): array
	{
		$vars = array();
		$vars['myusername'] = 'myusername';
		$vars['user'] = [
			'username' => 'myusername',
			'usernameletter' => 'm',
			'staff' => 0,
		];

		if (auth()->user())
		{
			$vars['myusername'] = auth()->user()->username;
			$vars['user']['username'] = auth()->user()->username;
			$vars['user']['usernameletter'] = substr(auth()->user()->username, 0, 1);
			$vars['user']['staff'] = (auth()->user()->can('manage knowledge') ? 1 : 0);
		}
		$vars['resource'] = (array)$this->params->get('variables', []);
		foreach ((array)$this->params->get('tags', []) as $tag)
		{
			if (in_array($tag, ['communitycluster', 'general', 'paidbutnonpbs', 'selfhome']))
			{
				$vars['access'] = ['type' => $tag];
			}
		}

		$vars['display'] = array('all' => false);
		if (request()->input('all'))
		{
			$vars['display']['all'] = true;
		}

		return $vars;
	}

	/**
	 * Merge variables
	 *
	 * @param   mixed  $collection
	 * @return  void
	 */
	public function mergeVariables($collection): void
	{
		$merged = $this->variables->mergeRecursive($collection);
		$this->varsRepository = $merged;
	}

	/**
	 * Get path
	 *
	 * @param   mixed  $path
	 * @return  string
	 */
	public function getPathAttribute($path): string
	{
		return $path ? $path : '/';
	}

	/**
	 * Retrieves one row loaded by an alias and parent_id fields
	 *
	 * @param   string  $alias
	 * @param   int     $parent_id
	 * @return  Page|null
	 */
	public static function findByAlias(string $alias, int $parent_id=0): ?Page
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
	public static function findByPath(string $path): ?Page
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
	 * @return  false|Collection    Boolean false on failure or array of node objects on success.
	 */
	public static function stackById(int $id)
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
	 * @return  array|bool   Boolean false on failure or array of node objects on success.
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
		array_shift($segments);

		if (empty($segments))
		{
			return $stack;
		}

		foreach ($segments as $segment)
		{
			$child = $parent->children()
				->where($parent->getTable() . '.alias', '=', $segment)
				->first();

			if (!$child)
			{
				return false;
			}

			$child->mergeVariables($parent->variables);

			$stack[] = $child;

			$parent = $child;
		}

		if ((count($stack) - 1) != count($segments))
		{
			return false;
		}

		return $stack;
	}

	/**
	 * Method to get a list of nodes from a given node to its root.
	 *
	 * @param   array  $filters
	 * @return  Collection
	 */
	public static function tree(array $filters = []): Collection
	{
		$p = (new self)->getTable();
		$a = (new Associations)->getTable();

		return self::query()
			->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.title', $a . '.level', $a . '.lft', $a . '.rgt', $a . '.id', $a . '.path')
			->orderBy('lft', 'asc')
			->get();
	}

	/**
	 * Get the root node
	 *
	 * @return  Page|null
	 */
	public static function rootNode(): ?Page
	{
		return self::query()
			->where('main', '=', 1)
			->limit(1)
			->first();
	}

	/**
	 * Get the creator of this entry
	 *
	 * @return  BelongsTo
	 */
	public function creator(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'created_by');
	}

	/**
	 * Get the modifier of this entry
	 *
	 * @return  BelongsTo
	 */
	public function modifier(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'updated_by');
	}

	/**
	 * Determine if record is the home Doc
	 * 
	 * @return  bool
	 */
	public function isRoot(): bool
	{
		return ($this->main == 1);
	}

	/**
	 * Determine if record was updated
	 * 
	 * @return  bool
	 */
	public function isModified(): bool
	{
		return !is_null($this->updated_at);
	}

	/**
	 * Determine if record is published
	 * 
	 * @return  bool
	 */
	public function isPublished(): bool
	{
		return ($this->state == 1);
	}

	/**
	 * Determine if record is published
	 * 
	 * @return  bool
	 */
	public function isArchived(): bool
	{
		return ($this->state == 2);
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
	 * Get child entries
	 *
	 * @return  Builder
	 */
	public function children(): Builder
	{
		$a = (new Associations)->getTable();
		$p = $this->getTable();

		// Assemble the query to find all children of this node.
		return self::query()
			->select($p . '.*')
			->join($a, $a . '.page_id', $p . '.id')
			->join($a . ' AS assoc2', 'assoc2.id', $a . '.parent_id')
			->where('assoc2.page_id', '=', (int) $this->id)
			->orderBy($a . '.parent_id', 'asc')
			->orderBy($a . '.lft', 'asc');

		//return $this->hasManyThrough(self::class, Associations::class, 'parent_id', 'id', 'id', 'page_id');
	}

	/**
	 * Get published children
	 *
	 * @return  Collection
	 */
	public function publishedChildren(): Collection
	{
		$a = (new Associations)->getTable();

		return $this->children()
			//->orderBy($a . '.lft', 'asc')
			->where('state', '=', 1)
			->whereIn('access', (auth()->user() ? auth()->user()->getAuthorisedViewLevels() : [1]))
			->get();
	}

	/**
	 * Get parent entries
	 *
	 * @return  HasManyThrough
	 */
	public function parents(): HasManyThrough
	{
		return $this->hasManyThrough(self::class, Associations::class, 'page_id', 'id', 'id', 'parent_id');
	}

	/**
	 * Get the count of associations
	 *
	 * @return  int
	 */
	public function getUsedAttribute(): int
	{
		return Associations::query()
			->where('page_id', '=', $this->page_id)
			->count();
	}
}
