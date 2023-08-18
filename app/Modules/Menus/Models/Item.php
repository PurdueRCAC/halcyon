<?php

namespace App\Modules\Menus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use App\Modules\Menus\Events\ItemCreating;
use App\Modules\Menus\Events\ItemCreated;
use App\Modules\Menus\Events\ItemUpdating;
use App\Modules\Menus\Events\ItemUpdated;
use App\Modules\Menus\Events\ItemDeleted;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Traits\Checkable;
use App\Halcyon\Models\Extension;
use App\Halcyon\Form\Form;
use App\Halcyon\Models\Casts\Params;
use Carbon\Carbon;
use Exception;

/**
 * Model for a menu item
 *
 * @property int    $id
 * @property string $menutype
 * @property string $title
 * @property string $alias
 * @property string $note
 * @property string $path
 * @property string $link
 * @property string $type
 * @property int    $published
 * @property int    $parent_id
 * @property int    $level
 * @property int    $module_id
 * @property int    $ordering
 * @property int    $checked_out
 * @property Carbon|null $checked_out_time
 * @property int    $target
 * @property int    $access
 * @property string $class
 * @property string $params
 * @property int    $lft
 * @property int    $rgt
 * @property int    $home
 * @property string $language
 * @property int    $client_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Item extends Model
{
	use Checkable, Historable, SoftDeletes;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'menu_items';

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
		'published' => 'integer',
		'access' => 'integer',
		'params' => Params::class,
		'checked_out_time' => 'datetime:Y-m-d H:i:s',
	];

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'creating' => ItemCreating::class,
		'created'  => ItemCreated::class,
		'updating' => ItemUpdating::class,
		'updated'  => ItemUpdated::class,
		'deleted'  => ItemDeleted::class,
		//'restored' => ItemRestored::class,
	];

	/**
	 * Set alias field value
	 *
	 * @param   string  $alias
	 * @return  void
	 */
	public function setAliasAttribute(string $alias): void
	{
		$alias = trim($alias);

		// Remove any '-' from the string since they will be used as concatenaters
		$alias = str_replace('-', ' ', $alias);
		$alias = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', strtolower($alias));
		$alias = trim($alias, '-');

		if (trim(str_replace('-', '', $alias)) == '')
		{
			$alias = Carbon::now()->format('Y-m-d-H-i-s');
		}

		$this->attributes['alias'] = $alias;
	}

	/**
	 * Get parent
	 *
	 * @return  BelongsTo
	 */
	public function parent(): BelongsTo
	{
		return $this->belongsTo(self::class, 'parent_id');
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
	 * Delete the record and all associated data
	 *
	 * @return  bool  False if error, True on success
	 */
	public function delete(): bool
	{
		// Remove children
		foreach ($this->children as $child)
		{
			$child->delete();
		}

		// Attempt to delete the record
		return parent::delete();
	}

	/**
	 * Save the record
	 *
	 * @param   array<string,mixed>  $options
	 * @return  bool   False if error, True on success
	 * @throws  Exception
	 */
	public function save(array $options = [])
	{
		if ($this->type == 'separator')
		{
			$this->title = trans('menus::menus.type separator');
		}

		if (!$this->alias)
		{
			$this->alias = $this->title;
		}

		if ($this->type == 'module' && $this->page_id)
		{
			if (strstr($this->page_id, '::'))
			{
				$bits = explode('::', $this->page_id);
				$this->page_id = $bits[1];
			}
			$page = \App\Modules\Pages\Models\Page::find($this->page_id);

			if ($page)
			{
				$this->path  = $page->path;
				$this->link  = $page->path;
				$this->alias = $page->alias;

				$module = Extension::findByModule('pages');

				$this->module_id = $module->id;
				$this->params->set('page_id', $this->page_id);
			}
		}
		$this->module_id = (int)$this->module_id;

		unset($this->page_id);

		if (!$this->access)
		{
			$this->access = (int) config('access', 1);
		}

		$isNew = !$this->id;

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

			if (!$parent->id)
			{
				throw new Exception(trans('Parent node does not exist.'));
			}

			// Get the reposition data for shifting the tree and re-inserting the node.
			$reposition = $this->getTreeRepositionData($parent, 2, 'last-child');

			// Shift left values.
			$query = self::query()
				->where($reposition->left_where['col'], $reposition->left_where['op'], $reposition->left_where['val'])
				->update([
					'lft' => DB::raw('lft + 2')
				]);

			/*if (!$query)
			{
				return false;
			}*/

			// Shift right values.
			$query = $this->getQuery()
				->where($reposition->right_where['col'], $reposition->right_where['op'], $reposition->right_where['val'])
				->update([
					'rgt' => DB::raw('rgt + 2')
				]);

			/*if (!$query)
			{
				return false;
			}*/

			// Set all the nested data
			if (!$this->path)
			{
				$this->path  = ($parent->path ? $parent->path . '/' : '') . $this->alias;
			}
			$this->lft   = $reposition->new_lft;
			$this->rgt   = $reposition->new_rgt;
			$this->level = $parent->level + 1;
		}

		//$this->params = $this->params->toString();

		$result = parent::save($options);

		/*if ($result)
		{
			$this->rebuildPath();

			foreach ($this->children as $child)
			{
				// Rebuild the tree path.
				if (!$child->rebuildPath())
				{
					return false;
				}
			}
		}*/

		return $result;
	}

	/**
	 * Method to recursively rebuild the whole nested set tree.
	 *
	 * @param   int  $parentId  The root of the tree to rebuild.
	 * @param   int  $leftId    The left id to start with in building the tree.
	 * @param   int  $level     The level to assign to the current nodes.
	 * @param   string   $path      The path to the current nodes.
	 * @param   string   $orderby
	 * @return  int  1 + value of root rgt on success, false on failure
	 */
	public function rebuild(int $parentId, int $leftId = 0, int $level = 0, string $path = '', string $orderby = 'lft')
	{
		// Assemble the query to find all children of this node.
		$children = self::query()
			->select(['id', 'alias'])
			->where('parent_id', '=', (int) $parentId)
			->orderBy('menutype', 'asc')
			->orderBy($orderby, 'asc')
			->get();

		// The right value of this node is the left value + 1
		$rightId = $leftId + 1;

		// execute this function recursively over all children
		foreach ($children as $node)
		{
			// $rightId is the current right value, which is incremented on recursion return.
			// Increment the level for the children.
			// Add this item's alias to the path (but avoid a leading /)
			$rightId = $this->rebuild($node->id, $rightId, $level + 1, $path . (empty($path) ? '' : '/') . $node->alias, $orderby);

			// If there is an update failure, return false to break out of the recursion.
			if ($rightId === false)
			{
				return false;
			}
		}

		// We've got the left value, and now that we've processed
		// the children of this node we also know the right value.
		$query = self::query()
			->where('id', '=', (int) $parentId)
			->update(array(
				'lft'   => (int) $leftId,
				'rgt'   => (int) $rightId,
				'level' => (int) $level,
				'path'  => $path
			));

		// If there is an update failure, return false to break out of the recursion.
		if (!$query)
		{
			return false;
		}

		// Return the right value of this node + 1.
		return $rightId + 1;
	}

	/**
	 * Get the root node
	 *
	 * @return  Item|null
	 */
	public static function rootNode()
	{
		return self::query()
			->where('level', '=', 0)
			->orderBy('lft', 'asc')
			->limit(1)
			->first();
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
	 * @return  bool|\stdClass  Boolean false on failure or data object on success.
	 */
	protected function getTreeRepositionData($referenceNode, int $nodeWidth, string $position = 'before')
	{
		// Make sure the reference an object with a left and right id.
		if (!is_object($referenceNode) || !isset($referenceNode->lft) || !isset($referenceNode->rgt))
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
	 * Get a form
	 *
	 * @return  Form
	 * @throws  Exception
	 */
	public function getForm(): Form
	{
		$file = __DIR__ . '/Forms/item.xml';

		Form::addFormPath(__DIR__ . '/Forms');
		Form::addFieldPath(__DIR__ . '/Fields');

		$form = new Form('item', array('control' => 'fields'));

		if (!$form->loadFile($file, false, '//form'))
		{
			throw new Exception(trans('global.load file failed'));
		}

		$data = $this->toArray();

		if ($this->type == 'module')
		{
			$data['page_id'] = $this->params->get('page_id');
			$data['route_id'] = 'pages::' . $this->params->get('page_id');
		}
		$data['params'] = $this->params->all();

		$form = $this->preprocessForm($form, $data);
		$form->bind($data);

		// Modify the form based on access controls.
		if (!auth()->user()
		 || !(($this->id && auth()->user()->can('edit.state menus.item.' . (int) $this->id)) || auth()->user()->can('edit.state menus')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('menuordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an article you can edit.
			$form->setFieldAttribute('menuordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * @param   Form    $form  A form object.
	 * @param   mixed   $data  The data expected for the form.
	 * @param   string  $group
	 * @return  Form
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm(Form $form, $data, $group = 'content'): Form
	{
		// Initialise variables.
		$link = $this->link;
		$type = $this->type;

		$formFile = false;

		// Initialise form with component view params if available.
		if ($type == 'module')
		{
			/*$link = htmlspecialchars_decode($link);

			// Parse the link arguments.
			$args = array();
			parse_str(parse_url(htmlspecialchars_decode($link), PHP_URL_QUERY), $args);

			// Confirm that the option is defined.
			$option = '';
			$base = '';
			if (isset($args['option']))
			{
				// The option determines the base path to work with.
				$option = $args['option'];
				$base   = $option . '/Resources/views/site';
			}*/
			$module = Extension::find($this->module_id);

			if ($module)
			{
				$option = $module->element;
				$base   = module_path($option) . '/Resources/views/site';
			}

			// Confirm a view is defined.
			$formFile = false;
			if (isset($args['view']))
			{
				$view = $args['view'];

				// Determine the layout to search for.
				if (isset($args['layout']))
				{
					$layout = $args['layout'];
				}
				else
				{
					$layout = 'index';
				}

				$formFile = false;

				// Check for the layout XML file. Use standard xml file if it exists.
				$path = $base . '/' . ($view ? $view . '/' : '') . $layout . '.xml';

				if (file_exists($path))
				{
					$formFile = $path;
				}
			}
		}

		if ($formFile)
		{
			// If an XML file was found in the module, load it first.
			// We need to qualify the full path to avoid collisions with module file names.
			if ($form->loadFile($formFile, true, '/metadata') == false)
			{
				throw new Exception(trans('menus::menus.error.load file failed'));
			}

			// Attempt to load the xml file.
			if (!$xml = simplexml_load_file($formFile))
			{
				throw new Exception(trans('menus::menus.error.load file failed'));
			}

			// Get the help data from the XML file if present.
			$help = $xml->xpath('/metadata/layout/help');
		}
		else
		{
			// We don't have a module. Load the form XML to get the help path
			$xmlFile = __DIR__ . '/Forms/item_' . $type . '.xml';

			if (file_exists($xmlFile))
			{
				// Attempt to load the xml file.
				if (!$xmlFile || ($xmlFile && !$xml = simplexml_load_file($xmlFile)))
				{
					throw new Exception(trans('menus::menus.error.load file failed'));
				}

				// Get the help data from the XML file if present.
				$help = $xml->xpath('/form/help');
			}
		}

		if (!empty($help))
		{
			$helpKey = trim((string) $help[0]['key']);
			$helpURL = trim((string) $help[0]['url']);
			$helpLoc = trim((string) $help[0]['local']);

			$this->helpKey = $helpKey ? $helpKey : $this->helpKey;
			$this->helpURL = $helpURL ? $helpURL : $this->helpURL;
			$this->helpLocal = (($helpLoc == 'true') || ($helpLoc == '1') || ($helpLoc == 'local')) ? true : false;
		}

		// Now load the module params.
		// TODO: Work out why 'fixing' this breaks Form
		if ($isNew = false)
		{
			$path = module_path($option) . '/Config/config.xml';
		}
		else
		{
			$path = 'null';
		}

		if (file_exists($path))
		{
			// Add the component params last of all to the existing form.
			if (!$form->load($path, true, '/config'))
			{
				throw new Exception(trans('menus::menus.error.load file failed'));
			}
		}

		// Load the specific type file
		if (!$form->loadFile(__DIR__ . '/Forms/item_' . $type . '.xml', false, false))
		{
			throw new Exception(trans('menus::menus.error.load file failed'));
		}

		// Association menu items
		/*if (app()->has('menu_associations') && app('menu_associations') != 0)
		{
			$languages = Lang::getLanguages('lang_code');

			$addform = new \SimpleXMLElement('<form />');
			$fields = $addform->addChild('fields');
			$fields->addAttribute('name', 'associations');
			$fieldset = $fields->addChild('fieldset');
			$fieldset->addAttribute('name', 'item_associations');
			$fieldset->addAttribute('description', 'menus::menus.item associations desc');

			$add = false;
			foreach ($languages as $tag => $language)
			{
				if ($tag != $data['language'])
				{
					$add = true;

					$field = $fieldset->addChild('field');
					$field->addAttribute('name', $tag);
					$field->addAttribute('type', 'menuitem');
					$field->addAttribute('language', $tag);
					$field->addAttribute('label', $language->title);
					$field->addAttribute('translate_label', 'false');

					$option = $field->addChild('option', 'menus::menus.item association none');
					$option->addAttribute('value', '');
				}
			}

			if ($add)
			{
				$form->load($addform, false);
			}
		}

		// Trigger the form preparation event.
		//event($event = new ContentPrepareForm($form, $data, $group ));

		$form = $event->form;
		*/

		return $form;
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
		$result = self::query()
			->where('id', '=', (int) $this->id)
			->update(array(
				'path' => $path
			));

		// Check for a database error.
		if (!$result)
		{
			return false;
		}

		// Update the current record's path to the new one:
		$this->path = $path;

		return true;
	}

	/**
	 * Method to move a row in the ordering sequence of a group of rows defined by an SQL WHERE clause.
	 * Negative numbers move the row up in the sequence and positive numbers move it down.
	 *
	 * @param   int  $delta  The direction and magnitude to move the row in the ordering sequence.
	 * @param   string   $where  WHERE clause to use for limiting the selection of rows to compact the ordering values.
	 * @return  bool    Boolean true on success.
	 * @throws  Exception
	 */
	public function move($delta, $where = ''): bool
	{
		$query = self::query()
			->where('parent_id', '=', $this->parent_id)
			->where('menutype', '=', $this->menutype);

		/*if ($where)
		{
			$query->whereRaw($where);
		}*/

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

		$referenceId = $query->get()->first()->id;

		if ($referenceId)
		{
			return $this->moveByReference($referenceId, $position, $this->id);
		}

		throw new Exception(trans('global.error.move failed') . ': Reference not found for delta ' . $delta);
	}

	/**
	 * Method to move a node and its children to a new location in the tree.
	 *
	 * @param   int     $referenceId  The primary key of the node to reference new location by.
	 * @param   string  $position     Location type string. ['before', 'after', 'first-child', 'last-child']
	 * @param   int     $pk           The primary key of the node to move.
	 * @return  bool    True on success.
	 * @throws  Exception
	 */
	public function moveByReference(int $referenceId, string $position = 'after', int $pk = 0)
	{
		// Initialise variables.
		$pk = (is_null($pk)) ? $this->id : $pk;

		// Get the node by id.
		$node = self::find($pk);

		if (!$node->id)
		{
			// Error message set in getNode method.
			throw new Exception(trans('global.error.move failed') . ': Node not found #' . $pk);
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
			throw new Exception(trans('global.error.invalid node recursion'));
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
				throw new Exception(trans('global.error.move failed') . ': Reference not found #' . $referenceId);
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
	 * Saves the manually set order of records.
	 *
	 * @param   array  $pks    An array of primary key ids.
	 * @param   array  $order  An array of order values.
	 * @return  bool
	 */
	public static function saveorder(array $pks = [], array $order = []): bool
	{
		if (empty($pks))
		{
			return false;
		}

		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$model = self::findOrFail((int) $pk);

			if ($model->ordering != $order[$i])
			{
				$model->ordering = $order[$i];

				if (!$model->save())
				{
					return false;
				}
			}
		}

		if (!$model->rebuild(1, 0, 0, '', 'ordering'))
		{
			return false;
		}

		return true;
	}
}
