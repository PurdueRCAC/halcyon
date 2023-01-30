<?php
namespace App\Widgets\Adminmenu;

use Illuminate\Support\Str;

/**
 * Menu node class
 */
class Node
{
	/**
	 * Parent node
	 * 
	 * @var  Node|null
	 */
	protected $_parent = null;

	/**
	 * Array of Children
	 *
	 * @var  array
	 */
	protected $_children = array();

	/**
	 * Node Title
	 *
	 * @var  string|null
	 */
	public $title = null;

	/**
	 * Node Id
	 *
	 * @var  string|null
	 */
	public $id = null;

	/**
	 * Node Link
	 *
	 * @var  string|null
	 */
	public $link = null;

	/**
	 * Link Target
	 *
	 * @var  string|null
	 */
	public $target = null;

	/**
	 * CSS Class for node
	 *
	 * @var  string|null
	 */
	public $class = null;

	/**
	 * Active Node?
	 *
	 * @var  bool
	 */
	public $active = false;

	/**
	 * Constructor
	 *
	 * @param   string  $title
	 * @param   string  $link
	 * @param   string  $class
	 * @param   bool    $active
	 * @param   string  $target
	 * @param   string  $titleicon
	 * @return  void
	 */
	public function __construct($title, $link = null, $class = null, $active = false, $target = null, $titleicon = null)
	{
		$this->title  = $titleicon ? $title . $titleicon : $title;
		$this->link   = $link;
		$this->class  = $class;
		$this->active = $active;

		$this->id = null;
		if (!empty($link) && $link !== '#')
		{
			$link = str_replace(array('https://', 'http://'), '', $link);
			$this->id = str_replace(array('.', '/'), '-', $link);
		}

		$this->target = $target;
	}

	/**
	 * Add child to this node
	 *
	 * If the child already has a parent, the link is unset
	 *
	 * @param   Node  &$child  The child to be added
	 * @return  void
	 */
	public function addChild(&$child)
	{
		if ($child instanceof Node)
		{
			$child->setParent($this);
		}
	}

	/**
	 * Set the parent of a this node
	 *
	 * If the node already has a parent, the link is unset
	 *
	 * @param   mixed  &$parent  The Node for parent to be set or null
	 * @return  void
	 */
	public function setParent(&$parent)
	{
		if ($parent instanceof Node || is_null($parent))
		{
			$hash = spl_object_hash($this);
			if (!is_null($this->_parent))
			{
				unset($this->_parent->children[$hash]);
			}
			if (!is_null($parent))
			{
				$parent->_children[$hash] = & $this;
			}
			$this->_parent = & $parent;
		}
	}

	/**
	 * Get the children of this node
	 *
	 * @return  array    The children
	 */
	public function &getChildren()
	{
		return $this->_children;
	}

	/**
	 * Get the parent of this node
	 *
	 * @return  Node|null   Node object with the parent or null for no parent
	 */
	public function &getParent()
	{
		return $this->_parent;
	}

	/**
	 * Test if this node has children
	 *
	 * @return  bool  True if there are children
	 */
	public function hasChildren()
	{
		return (bool) count($this->_children);
	}

	/**
	 * Test if this node has a parent
	 *
	 * @return  bool  True if there is a parent
	 */
	public function hasParent()
	{
		return $this->getParent() != null;
	}
}
