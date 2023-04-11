<?php

namespace App\Modules\Knowledge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Config\Repository;
use App\Modules\History\Traits\Historable;
use App\Modules\Knowledge\Events\PageCreating;
use App\Modules\Knowledge\Events\PageCreated;
use App\Modules\Knowledge\Events\PageUpdating;
use App\Modules\Knowledge\Events\PageUpdated;
use App\Modules\Knowledge\Events\PageDeleted;
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
	 * Regex patterns for in-content IF statements
	 *
	 * @var string
	 */
	const REGEXP_VARIABLE = "/\\\$\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)(([\*\/\-\+])(\d+(\.\d+)?))?\}/";
	const REGEXP_IF_STATEMENT = "/\{::if\s+([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(==|!=|>|>=|<|<=|=~)\s*([^\}]+)\s*\}(.+?)\{::\/\}/s";
	const REGEXP_IF_ELSE = "/\{::elseif\s+([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(==|!=|>|>=|<|<=|=~)\s*([^\}]+)\s*\}(.+?)(?=\{::)/s";
	const REGEXP_IF = "/\{::if\s+([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(==|!=|>|>=|<|<=|=~)\s*([^\}]+)\s*\}_\d+(.+?)(?=\{::)/s";
	const REGEXP_ELSE = "/\{::else\}(.+?)(?=\{::)/s";
	const REGEXP_LINK = "/\[(.+?)\]\((.+?)\)/";

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
		'state' => 'integer',
		'access' => 'integer',
		'main' => 'integer',
		'snippet' => 'integer',
		'params' => Params::class,
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array<string,string>
	 */
	protected $rules = array(
		'title' => 'required|string|max:255',
		'alias' => 'required|string|max:255'
	);

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
	 * @var  object
	 */
	protected $varsRepository = null;

	/**
	 * Page metadata
	 *
	 * @var  object
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
		$text = $this->title;
		$text = preg_replace_callback(self::REGEXP_VARIABLE, array($this, 'replaceVariables'), $text);

		return $text;
	}

	/**
	 * Get content with variables replaced
	 *
	 * @return  string
	 */
	public function getBodyAttribute(): string
	{
		$text = $this->content;

		$text = preg_replace_callback(self::REGEXP_VARIABLE, array($this, 'replaceVariables'), $text);
		$text = preg_replace_callback('/(\{::if\s+.*?\})(.*?{::\/\})/s', array($this, 'tokenizeIf'), $text);
		for (self::$matches; self::$matches > 0; self::$matches--)
		{
			$m = self::$matches;
			$text = preg_replace_callback("/\{::if\s+([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(==|!=|>|>=|<|<=|=~)\s*([^\}]+)\s*\}_$m(.+?)\{::\/\}/s", array($this, 'replaceIfStatement'), $text);
		}
		//$text = preg_replace_callback(self::REGEXP_IF_STATEMENT, array($this, 'replaceIfStatement'), $text);
		//$text = preg_replace_callback(self::REGEXP_LINK, array($this, 'replaceLink'), $text);

		$text = preg_replace("/<p>(.*)<\/p>\n<(table.*)\n/m", "<$2 <caption>$1</caption>\n", $text);
		$text = preg_replace("/<h2>(.*)<\/h2>/", "<h3>$1</h3>", $text);
		$text = preg_replace("/<h1>(.*)<\/h1>/", "<h2>$1</h2>", $text);

		$text = preg_replace('/href="\/(.*?)"/i', 'href="' . url("$1") . '"', $text);

		// Fix file paths
		$text = preg_replace_callback('/\ssrc="(.*?)"/i', function($matches)
		{
			if (substr($matches[1], 0, 4) == 'http')
			{
				return ' src="' . $matches[1] . '"';
			}

			if (substr($matches[1], 0, 7) == '/files/')
			{
				$matches[1] = substr($matches[1], 7);
			}

			if (substr($matches[1], 0, 6) == 'files/')
			{
				$matches[1] = substr($matches[1], 6);
			}

			return ' src="' . asset("files/" . ltrim($matches[1], '/')) . '"';
		}, $text);
		//$text = preg_replace('/src="(.*?)"/i', 'src="' . asset("files/$1") . '"', $text);
		$text = preg_replace('/src="\/include\/images\/(.*?)"/i', 'src="' . asset("files/$1") . '"', $text);
		$text = preg_replace('/href="\/(.*?)"/i', 'href="' . url("$1") . '"', $text);

		$headers = array();
		$toc = array();

		$text = preg_replace_callback( '/(\<h[1-6](.*?))\>(.*)(<\/h[1-6]>)/i', function($matches) use (&$headers, &$toc)
		{
			if (!stripos($matches[0], 'id='))
			{
				$title = $matches[3];
				$title = preg_replace('/<.*?>/', '', $title);
				$title = trim($title);
				$title = strtolower($title);
				$title = str_replace(' ', '_', $title);
				$title = preg_replace('/[^a-z0-9\-_]+/', '', $title);
				$title = (request('all') ? $this->id . '-' : '') . $title;

				if (!isset($headers[$title]))
				{
					$headers[$title] = 0;
				}
				$headers[$title]++;

				if ($headers[$title] > 1)
				{
					$title = $headers[$title] . '_' . $title;
				}
				$toc[$title] = $matches[3];

				$anchor = '<a href="#' . $title . '" class="heading-anchor" title="Link to section \'' . e(strip_tags($matches[3])) . '\' of \'' . e(strip_tags($this->headline)) . '\'"><span class="fa fa-link" aria-hidden="true"></span><span class="sr-only">Link to section \'' . strip_tags($matches[3]) . '\' of \'' . strip_tags($this->headline) . '\'</span></a> ';

				$matches[0] = $matches[1] . ' id="' . $title . '">' . $anchor . $matches[3] . $matches[4];
			}
			else
			{
				$matches[0] = preg_match('/id="(.*?)"/i', $matches[0], $matcs);

				$title = $matcs[1];

				$anchor = '<a href="#' . $title . '" class="heading-anchor" title="Link to section \'' . e(strip_tags($matches[3])) . '\' of \'' . e(strip_tags($this->headline)) . '\'"><span class="fa fa-link" aria-hidden="true"></span><span class="sr-only">Link to section \'' . strip_tags($matches[3]) . '\' of \'' . strip_tags($this->headline) . '\'</span></a> ';

				$matches[0] = $matches[1] . '>' . $anchor . $matches[3] . $matches[4];
			}
			return $matches[0];
		}, $text);

		$t = '';
		if ($this->params->get('show_page_toc'))
		{
			$t .= '<div class="table-of-contents">';
			$t .= '<ul>';
			foreach ($toc as $anchor => $header)
			{
				$t .= '<li><a href="#' . $anchor . '">' . $header. '</a></li>';
			}
			$t .= '</ul>';
			$t .= '</div>';
		}

		return $t . $text;
	}

	/**
	 * Nesting counter
	 * 
	 * @var  int
	 */
	private static $matches = 0;

	/**
	 * Add a token to IF statements to determine proper nesting
	 *
	 * @param   array<int,string>  $matches
	 * @return  string
	 */
	private function tokenizeIf(array $matches): string
	{
		self::$matches++;

		if (count($matches) == 1)
		{
			return $matches[0] . '_' . self::$matches;
		}

		return $matches[1] . '_' . self::$matches . preg_replace_callback("/\{::if\s+.*?\}/", array($this, 'tokenizeIf'), $matches[2]);
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
	 * @param   mixed   $collection
	 * @return  void
	 */
	public function mergeVariables($collection): void
	{
		$merged = $this->variables->mergeRecursive($collection);
		$this->varsRepository = $merged;
	}

	/**
	 * Replace variables
	 *
	 * @param   array   $matches
	 * @return  string
	 */
	protected function replaceVariables(array $matches)
	{
		$vars = $this->variables->toArray();

		if (isset($vars[$matches[1]][$matches[2]]))
		{
			$val = $vars[$matches[1]][$matches[2]];
			if (is_array($val))
			{
				$val = array_shift($val);
			}

			if (isset($matches[5]) && is_numeric($val) && is_numeric($matches[5]))
			{
				if ($matches[4] == '+')
				{
					return $val + $matches[5];
				}
				elseif ($matches[4] == '-')
				{
					return $val - $matches[5];
				}
				elseif ($matches[4] == '/')
				{
					return $val / $matches[5];
				}
				elseif ($matches[4] == '*')
				{
					return $val * $matches[5];
				}
			}

			return $val;
		}

		return $matches[0];
	}

	/**
	 * Replace links
	 *
	 * @param   array   $matches
	 * @return  string
	 */
	protected function replaceLink(array $matches)
	{
		$branch = '';

		if (isset($_GET['branch']))
		{
			$branch = '?branch=' . str_replace(array('"', "'"), '', urldecode($_GET['branch']));
		}

		// Don't touch real links
		// Don't touch links anchored at doc root
		if (!preg_match("/^https?\:\/\//", $matches[2]) && !preg_match("/^\//", $matches[2]))
		{
				$path = preg_replace("/\/README.md/", '', $this->curItem->path);

				// Append together, collapse any .. monikers
				$realurl =  $this->getAbsolutePath($path . '/' . $matches[2]);

				if (preg_match("@^" . $this->cwd . "@", $realurl))
				{
					// This is inside the current expansion, need anchor text
					$anchor = preg_replace("@/@", '_', $realurl);
					$url = '[' . $matches[1] . '](#' . $anchor . ')';
				}
				else
				{
					// This is outside current expansion, return as is
					$url = '[' . $matches[1] . '](/knowledge/' . $this->tag . '/' . $realurl . $branch . ')';
				}

				return $url;
		}

		// Not touching it, return it back as it was
		return $matches[0];
	}

	/**
	 * Replace "if" statements
	 *
	 * @param   array   $matches
	 * @return  string
	 */
	protected function replaceIfStatement(array $matches): string
	{
		$vars = $this->variables->toArray();

		$clauses = array();

		// Pull out an else
		$else_output = null;
		$else = array();
		if (preg_match(self::REGEXP_ELSE, $matches[0], $else))
		{
			$else_output = $else[1];
		}

		// See if we have any if elses
		$elses = array();
		preg_match_all(self::REGEXP_IF_ELSE, $matches[0], $elses, PREG_SET_ORDER);

		if (count($elses) == 0)
		{
			// Break out first if
			$if = array();

			preg_match(self::REGEXP_IF, $matches[0], $if);

			array_push($clauses, array(
				'tag'      => $if[1],
				'var'      => $if[2],
				'operator' => $if[3],
				'value'    => $if[4],
				'output'   => $if[5],
			));
		}
		else
		{
			// Break out first if
			$if = array();

			preg_match(self::REGEXP_IF, $matches[0], $if);

			array_push($clauses, array(
				'tag'      => $if[1],
				'var'      => $if[2],
				'operator' => $if[3],
				'value'    => $if[4],
				'output'   => $if[5],
			));

			foreach ($elses as $else)
			{
				array_push($clauses, array(
					'tag'      => $else[1],
					'var'      => $else[2],
					'operator' => $else[3],
					'value'    => $else[4],
					'output'   => $else[5],
				));
			}
		}

		// Process clauses
		foreach ($clauses as $clause)
		{
			$operator = $clause['operator'];
			$right    = trim($clause['value']);
			$result   = false;

			if (isset($vars[$clause['tag']][$clause['var']]))
			{
				if (is_array($vars[$clause['tag']][$clause['var']]))
				{
					$vars[$clause['tag']][$clause['var']] = array_shift($vars[$clause['tag']][$clause['var']]);
				}
				$left = trim($vars[$clause['tag']][$clause['var']]);

				$left = (is_integer($left) ? (int)$left : $left);
				$left = (strtolower($left) === 'true' ? true : $left);
				$left = (strtolower($left) === 'false' ? false : $left);

				$right = (is_integer($right) ? (int)$right : $right);
				$right = (strtolower($right) === 'true' ? true : $right);
				$right = (strtolower($right) === 'false' ? false : $right);

				if ($operator == '==')
				{
					if ($right === true)
					{
						$result = ($left ? true : false);
					}
					elseif ($right === false)
					{
						$result = (!$left ? true : false);
					}
					else
					{
						$result = ($left == $right ? true : false);
					}
				}
				elseif ($operator == '!=')
				{
					if ($right === true)
					{
						$result = (!$left ? true : false);
					}
					elseif ($right === false)
					{
						$result = ($left ? true : false);
					}
					else
					{
						$result = ($left != $right ? true : false);
					}
				}
				elseif ($operator == '>')
				{
					$result = ($left > $right ? true : false);
				}
				elseif ($operator == '<')
				{
					$result = ($left < $right ? true : false);
				}
				elseif ($operator == '<=')
				{
					$result = ($left <= $right ? true : false);
				}
				elseif ($operator == '>=')
				{
					$result = ($left >= $right ? true : false);
				}
				elseif ($operator == '=~')
				{
					$result = (preg_match("/$right/i", $left) ? true : false);
				}
			}
			else
			{
				$result = false;
			}

			if ($result)
			{
				// Strip leading or trailing space
				$output = preg_replace("/\s+$/", ' ', $clause['output']);
				// Strip leading newlines
				$output = preg_replace("/^ *\n/", '', $output);
				return $output;
			}
		}

		// If we failed everything, return the elseif we have one.
		if ($else_output != null)
		{
			// Strip leading or trailing space
			$else_output = preg_replace("/\s+$/", ' ', $else_output);
			$else_output = preg_replace("/^ *\n/", '', $else_output);
			return $else_output;
		}

		return '';
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
	public static function findByAlias(string $alias, int $parent_id=0)
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
	public static function findByPath(string $path)
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
				->get()
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
	 * @param   string  $path  Primary key of the node for which to get the path.
	 * @return  mixed   Boolean false on failure or array of node objects on success.
	 */
	public static function tree(array $filters = [])
	{
		$p = (new self)->getTable();
		$a = (new Associations)->getTable();

		$results = self::query()
			->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.title', $a . '.level', $a . '.lft', $a . '.rgt', $a . '.id', $a . '.path')
			->orderBy('lft', 'asc')
			->get();

		return $results;
	}

	/**
	 * Recursive function to build tree
	 *
	 * @param   array  $list      List of records
	 * @param   array  $children  Container for parent/children mapping
	 * @param   int    $maxlevel  Maximum levels to descend
	 * @param   int    $level     Indention level
	 * @return  array
	 */
	protected static function treeRecurse($list, $children, int $maxlevel=9999, int $level=0)
	{
		if (count($children) && $level <= $maxlevel)
		{
			foreach ($children as $child)
			{
				$id = $child->id;

				$child->level = $level;

				$list[$id] = $child;

				$list = self::treeRecurse($list, $child->children()->orderBy('ordering', 'asc')->get(), $maxlevel, $level+1);
			}
		}
		return $list;
	}

	/**
	 * Get the root node
	 *
	 * @return  Page|null
	 */
	public static function rootNode()
	{
		return self::query()
			->where('main', '=', 1)
			->limit(1)
			->get()
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
	 * @return  object
	 */
	public function children()
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
	 * @return  object  collection
	 */
	public function publishedChildren()
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
	 * @return  object
	 */
	public function parents()
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
