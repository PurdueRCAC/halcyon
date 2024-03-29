<?php
namespace App\Widgets\Adminmenu;

/**
 * Extended class for rendering nested menus
 */
class Tree
{
	/**
	 * CSS string to add to document head
	 *
	 * @var  string
	 */
	protected $_css = null;

	/**
	 * Root node
	 *
	 * @var  Node
	 */
	protected $_root;

	/**
	 * Current working node
	 *
	 * @var  Node
	 */
	protected $_current;

	/**
	 * Constructor
	 *
	 * @return  void
	 */
	public function __construct()
	{
		$this->_root = new Node('ROOT');
		$this->_current =& $this->_root;
	}

	/**
	 * Method to add a child
	 *
	 * @param   Node  $node       The node to process
	 * @param   bool  $setCurrent  True to set as current working node
	 * @return  void
	 */
	public function addChild($node, $setCurrent = false)
	{
		$this->_current->addChild($node);

		if ($setCurrent)
		{
			$this->_current = &$node;
		}
	}

	/**
	 * Method to get the parent
	 *
	 * @return  Tree
	 */
	public function getParent()
	{
		$this->_current = &$this->_current->getParent();

		return $this;
	}

	/**
	 * Method to get the parent
	 *
	 * @return  Tree
	 */
	public function reset()
	{
		$this->_current = &$this->_root;

		return $this;
	}

	/**
	 * Add a separator
	 *
	 * @return  Tree
	 */
	public function addSeparator()
	{
		$this->addChild(new Node(null, null, 'separator', false));

		return $this;
	}

	/**
	 * Render the menu
	 *
	 * @param   string  $id     Menu ID
	 * @param   string  $class  Menu class
	 * @return  void
	 */
	public function renderMenu($id = 'adminmenu', $class = '')
	{
		$depth = 1;

		if (!empty($id))
		{
			$id = 'id="' . $id . '"';
		}

		if (!empty($class))
		{
			$class = 'class="' . $class . '"';
		}

		// Recurse through children if they exist
		while ($this->_current->hasChildren())
		{
			echo '<ul ' . $id . ' ' . $class . ">\n";
			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current =& $child;
				$this->renderLevel($depth+1);
			}
			echo "</ul>\n";
		}
	}

	/**
	 * Render a menu level
	 *
	 * @param   int $depth
	 * @return  void
	 */
	public function renderLevel($depth)
	{
		// Build the CSS class suffix
		$class = '';
		$iconClass = '';
		$classes = array();

		if ($this->_current->class)
		{
			$classes = explode(' ', trim($this->_current->class));
			foreach ($classes as $i => $clas)
			{
				if (substr($clas, 0, strlen('class:')) == 'class:')
				{
					$iconClass = $clas;
					unset($classes[$i]);
				}
			}
		}

		if ($this->_current->hasChildren())
		{
			$classes[] = 'node';
		}

		if ($this->_current->active)
		{
			$classes[] = 'active';
		}

		if (!empty($classes))
		{
			$class = ' class="' . implode(' ', $classes) . '"';
		}

		// Print the item
		echo str_repeat("\t", $depth) . '<li' . $class . ($this->_current->hasChildren() || $depth > 2 ? '' : ' data-title="' . strip_tags($this->_current->title) . '"') . '>';// . "\n";

		// Print a link if it exists
		$linkClass = $this->getIconClass($iconClass);
		if (!empty($linkClass))
		{
			$linkClass = ' class="' . $linkClass . '"';
		}

		//echo str_repeat("\t", $depth + 1);

		if ($this->_current->link != null && $this->_current->target != null)
		{
			echo '<a' . $linkClass . ' href="' . $this->_current->link . '" rel="noopener" target="' . $this->_current->target . '">' . $this->_current->title . '</a>';
		}
		elseif ($this->_current->link != null && $this->_current->target == null)
		{
			echo '<a' . $linkClass . ' href="' . $this->_current->link . '">' . $this->_current->title . '</a>';
		}
		elseif ($this->_current->title != null)
		{
			echo '<a' . $linkClass . '>' . $this->_current->title . '</a>';
		}
		else
		{
			echo '<span></span>';
		}

		//echo "\n";

		// Recurse through children if they exist
		while ($this->_current->hasChildren())
		{
			if ($this->_current->class)
			{
				$id = '';
				if (!empty($this->_current->id))
				{
					$id = ' id="menu-' . strtolower($this->_current->id) . '"';
				}
				echo "\n" . str_repeat("\t", $depth + 1) . '<ul' . $id . ' class="menu-component">' . "\n";
			}
			else
			{
				echo "\n" . str_repeat("\t", $depth + 1) . '<ul>' . "\n";
			}

			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current =& $child;
				$this->renderLevel($depth+2);
			}

			echo str_repeat("\t", $depth + 1) . "</ul>\n" . str_repeat("\t", $depth);
		}
		echo "</li>\n";
	}

	/**
	 * Method to get the CSS class name for an icon identifier or create one if
	 * a custom image path is passed as the identifier
	 *
	 * @param   string  $identifier  Icon identification string
	 * @return  string  CSS class name
	 */
	public function getIconClass($identifier)
	{
		static $classes;

		// Initialise the known classes array if it does not exist
		if (!is_array($classes))
		{
			$classes = array();
		}

		// If we don't already know about the class... build it and mark it
		// known so we don't have to build it again
		if (!isset($classes[$identifier]))
		{
			if (substr($identifier, 0, 6) == 'class:')
			{
				// We were passed a class name
				$class = substr($identifier, 6);
				$classes[$identifier] = "icon-$class";
			}
			else
			{
				if ($identifier == null)
				{
					return null;
				}

				// Build the CSS class for the icon
				$class = preg_replace('#\.[^.]*$#', '', basename($identifier));
				$class = preg_replace('#\.\.[^A-Za-z0-9\.\_\- ]#', '', $class);

				$this->_css  .= "\n.icon-$class {\n" .
						"\tbackground: url($identifier) no-repeat;\n" .
					"}\n";

				$classes[$identifier] = "icon-$class";
			}
		}

		return $classes[$identifier];
	}
}
