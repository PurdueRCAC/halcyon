<?php
/**
 * @package   halcyon-cms
 * @copyright Copyright 2005-2015 Halcyon Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Knowledge\Models;

use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Halcyon\Config\Registry;
use App\Modules\History\Traits\Historable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Knowledge\Events\PageCreating;
use App\Modules\Knowledge\Events\PageCreated;
use App\Modules\Knowledge\Events\PageUpdating;
use App\Modules\Knowledge\Events\PageUpdated;
use App\Modules\Knowledge\Events\PageDeleted;
use App\Halcyon\Models\Casts\Params;

/**
 * Model class for a page
 */
class Page extends Model
{
	use ErrorBag, Validatable, Historable, SoftDeletes;

	const REGEXP_VARIABLE = "/\\\$\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)(([\*\/\-\+])(\d+(\.\d+)?))?\}/";
	const REGEXP_IF_STATEMENT = "/\{::if\s+([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(==|!=|>|>=|<|<=|=~)\s*([^\}]+)\s*\}(.+?)\{::\/\}/s";
	const REGEXP_IF_ELSE = "/\{::elseif\s+([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(==|!=|>|>=|<|<=|=~)\s*([^\}]+)\s*\}(.+?)(?=\{::)/s";
	const REGEXP_IF = "/\{::if\s+([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\s*(==|!=|>|>=|<|<=|=~)\s*([^\}]+)\s*\}(.+?)(?=\{::)/s";
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
		'id'
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
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
	 * @var  array
	 */
	protected $rules = array(
		'title' => 'required|string|max:255',
		'alias' => 'required|string|max:255'
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
	 * Registry
	 *
	 * @var  object
	 */
	protected $varsRegistry = null;

	/**
	 * Does the Doc exist?
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
	 * Get a params Registry object
	 *
	 * @return  object
	 */
	/*public function getOptionsAttribute()
	{
		if (!($this->paramsRegistry instanceof Registry))
		{
			$this->paramsRegistry = new Registry($this->params);
		}

		return $this->paramsRegistry;
	}*/

	/**
	 * Get a params Registry object
	 *
	 * @return  object
	 */
	public function getVariablesAttribute()
	{
		if (!($this->varsRegistry instanceof Registry))
		{
			$this->varsRegistry = new Registry($this->params->get('variables', []));
		}

		return $this->varsRegistry;
	}

	/**
	 * Get title with variables replaced
	 *
	 * @return  string
	 */
	public function getHeadlineAttribute()
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
	public function getBodyAttribute()
	{
		$text = $this->content;
		$text = preg_replace_callback(self::REGEXP_VARIABLE, array($this, 'replaceVariables'), $text);
		$text = preg_replace_callback(self::REGEXP_IF_STATEMENT, array($this, 'replaceIfStatement'), $text);
		//$text = preg_replace_callback(self::REGEXP_LINK, array($this, 'replaceLink'), $text);

		$text = preg_replace("/<p>(.*)<\/p>\n<(table.*)\n/m", "<$2 <caption>$1</caption>\n", $text);
		$text = preg_replace("/<h2>(.*)<\/h2>/", "<h3 class=\"kb2\">$1</h3>", $text);
		$text = preg_replace("/<h1>(.*)<\/h1>/", "<h2 class=\"kb1\">$1</h2>", $text);

		$text = preg_replace('/href="\/(.*?)"/i', 'href="' . url("$1") . '"', $text);

		// Fix file paths
		$text = preg_replace('/src="(.*?)"/i', 'src="' . asset("files/$1") . '"', $text);
		$text = preg_replace('/src="\/include\/images\/(.*?)"/i', 'src="' . asset("files/$1") . '"', $text);
		$text = preg_replace('/href="\/(.*?)"/i', 'href="' . url("$1") . '"', $text);

		return $text;
	}

	/**
	 * Get variables for replacement
	 *
	 * @return  array
	 */
	private function getVars()
	{
		$vars = array();
		$vars['user'] = ['username' => 'myusername'];
		if (auth()->user())
		{
			$vars['user']['username'] = auth()->user()->username;
		}
		$vars['resource'] = $this->variables->toArray(); //(array)$this->params->get('variables', []);
		foreach ((array)$this->params->get('tags', []) as $tag)
		{
			if (in_array($tag, ['communitycluster', 'general', 'paidbutnonpbs']))
			{
				$vars['access'] = ['type' => $tag];
			}
		}

		return $vars;
	}

	/**
	 * Replace variables
	 *
	 * @param   array   $matches
	 * @return  string
	 */
	protected function replaceVariables($matches)
	{
		$vars = $this->getVars();

		if (isset($vars[$matches[1]][$matches[2]]))
		{
			$val = $vars[$matches[1]][$matches[2]];

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

			return $vars[$matches[1]][$matches[2]];
		}

		return $matches[0];
	}

	/**
	 * Replace links
	 *
	 * @param   array   $matches
	 * @return  string
	 */
	protected function replaceLink($matches)
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
	protected function replaceIfStatement($matches)
	{
		$vars = $this->getVars();

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
			$right    = $clause['value'];
			$result   = false;

			if (isset($vars[$clause['tag']][$clause['var']]))
			{
				$left = $vars[$clause['tag']][$clause['var']];

				if ($operator == '==')
				{
					$result = ($left == $right ? true : false);
				}
				elseif ($operator == '!=')
				{
					$result = ($left != $right ? true : false);
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
	 * @return  object
	 */
	public function getPathAttribute($path)
	{
		return $path ? $path : '/';
	}

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
				//->where('snippet', '=', 0)
				->get()
				->first();

			if (!$child)
			{
				return false;
			}

			$child->variables->merge($parent->variables);

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
	public static function tree($filters = array())
	{
		/*$model = new self();

		$p = $model->getTable();
		$a = (new Association)->getTable();

		// Get the path from the node to the root.
		$results = self::query()
			->select('p.*')
			->from($p . ' AS n')
			->join($a . ' AS p', 'p.parent_id', '>', DB::raw('0'))
			->whereRaw('n.lft BETWEEN p.lft AND p.rgt')
			->where('n.path', '=', (string) $path)
			->orderBy('p.lft', 'asc')
			->get();*/
		$p = (new self)->getTable();
		$a = (new Associations)->getTable();

		$results = self::query()
			->join($a, $a . '.page_id', $p . '.id')
			->select($p . '.title', $a . '.level', $a . '.lft', $a . '.rgt', $a . '.id', $a . '.path')
			->orderBy('lft', 'asc')
			->get();

		return $results;

		/*$root = self::rootNode();
		$root->level = 0;

		$list = array();
		$list[$root->id] = $root; //->id;

		$levellimit = (!isset($filters['limit']) || $filters['limit'] == 0) ? 500 : $filters['limit'];
		$list = self::treeRecurse($list, $root->children()->orderBy('ordering', 'asc')->get(), max(0, $levellimit-1), 1);

		return $list;*/
	}

	/**
	 * Recursive function to build tree
	 *
	 * @param   integer  $id        Parent ID
	 * @param   string   $indent    Indent text
	 * @param   array    $list      List of records
	 * @param   array    $children  Container for parent/children mapping
	 * @param   integer  $maxlevel  Maximum levels to descend
	 * @param   integer  $level     Indention level
	 * @param   integer  $type      Indention type
	 * @return  array
	 */
	protected static function treeRecurse($list, $children, $maxlevel=9999, $level=0)
	{
		if (count($children) && $level <= $maxlevel)
		{
			foreach ($children as $child)
			{
				$id = $child->id;

				$child->level = $level;

				$list[$id] = $child; //str_repeat('<span class="gi">|&mdash;</span>', $level) . $child->title;

				$list = self::treeRecurse($list, $child->children()->orderBy('ordering', 'asc')->get(), $maxlevel, $level+1);
			}
		}
		return $list;
	}

	/**
	 * Get the root node
	 *
	 * @return  object
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
	 * Determine if record is the home Doc
	 * 
	 * @return  boolean
	 */
	public function isRoot()
	{
		return ($this->main == 1);
	}

	/**
	 * Determine if record was updated
	 * 
	 * @return  boolean
	 */
	public function isModified()
	{
		return ($this->updated_at && $this->updated_at != '0000-00-00 00:00:00');
	}

	/**
	 * Determine if record is published
	 * 
	 * @return  boolean
	 */
	public function isPublished()
	{
		return ($this->state == 1);
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
	 * Get child entries
	 *
	 * @return  object
	 */
	public function children()
	{
		//return $this->hasManyThrough(self::class, Association::class, 'parent_id', 'id', 'id', 'child_id');
		$a = (new Associations)->getTable();
		$p = $this->getTable();

		/*return self::query()
			->select($p . '.*')
			->join($a, $a . '.page_id', $p . '.id')
			->join($a . ' AS assoc2', 'assoc2.id', $a . '.parent_id')
			->where($a . '.page_id', '=', (int) $this->id)
			->where($a . '.lft', '>', 'assoc2.lft')
			->where($a . '.rgt', '<', 'assoc2.rgt')
			->orderBy($a . '.parent_id', 'asc')
			->orderBy($a . '.lft', 'asc');
		echo self::query()
			->select($p . '.*')
			->from($p)
			->join($a . ' AS n', 'n.page_id', $p . '.id')
			->join($a . ' AS p', 'p.level', '>', \Illuminate\Support\Facades\DB::raw(0))
			->whereRaw('n.lft BETWEEN p.lft AND p.rgt')
			->where('n.page_id', '=', (int) $this->id)
			->orderBy('p.lft', 'asc')
			->toSql(); die();
		return self::query()
			->select($p . '.*')
			->from($p)
			->join($a . ' AS n', 'n.page_id', $p . '.id')
			->join($a . ' AS p', 'p.level', '>', \Illuminate\Support\Facades\DB::raw(0))
			->whereRaw('n.lft BETWEEN p.lft AND p.rgt')
			->where('n.page_id', '=', (int) $this->id)
			->orderBy('p.lft', 'asc');*/

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
		//return $this->hasManyThrough(self::class, Association::class, 'child_id', 'id', 'id', 'parent_id');
		return $this->hasManyThrough(self::class, Associations::class, 'page_id', 'id', 'id', 'parent_id');
	}

	/**
	 * Defines a relationship to a parent page
	 *
	 * @return  object
	 */
	public function getUsedAttribute()
	{
		return Associations::query()
			->where('page_id', '=', $this->page_id)
			->count();
	}
}
