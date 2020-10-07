<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Html;

/**
 * ToolBar handler
 */
class Toolbar
{
	/**
	 * Toolbar name
	 *
	 * @var  string
	 */
	protected $_name = '';

	/**
	 * Toolbar array
	 *
	 * @var  array
	 */
	protected $_bar = array();

	/**
	 * Loaded buttons
	 *
	 * @var  array
	 */
	protected $_buttons = array();

	/**
	 * Directories, where button types can be stored.
	 *
	 * @var  array
	 */
	protected $_buttonPath = array();

	/**
	 * Constructor
	 *
	 * @param   string  $name  The toolbar name
	 * @return  void
	 */
	public function __construct($name = 'toolbar')
	{
		$this->_name = $name;

		// Set base path to find buttons.
		$this->_buttonPath[] = __DIR__ . DIRECTORY_SEPARATOR . 'Toolbar' . DIRECTORY_SEPARATOR . 'Button';
	}

	/**
	 * Push button onto the end of the toolbar array.
	 *
	 * @return  object
	 */
	public function append()
	{
		$btn = func_get_args();

		array_push($this->_bar, $btn);

		return $this;
	}

	/**
	 * Get the list of toolbar links.
	 *
	 * @return  array
	 */
	public function all()
	{
		return $this->_bar;
	}

	/**
	 * Get the name of the toolbar.
	 *
	 * @return  string
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Insert button into the front of the toolbar array.
	 *
	 * @return  object
	 */
	public function prepend()
	{
		$btn = func_get_args();

		array_unshift($this->_bar, $btn);

		return $this;
	}

	/**
	 * Render a tool bar.
	 *
	 * @return  string  HTML for the toolbar.
	 */
	public function render()
	{
		$html = array();

		// Start toolbar div.
		$html[] = '<div class="toolbar-btn-list" id="' . $this->_name . '">';
		$html[] = '<ul>';

		foreach ($this->_bar as $key => $button)
		{
			$this->_bar[$key][9] = array();

			if ($button[0] == 'Separator')
			{
				continue;
			}

			if (!isset($this->_bar[$key - 1]) || $this->_bar[$key - 1][0] == 'Separator')
			{
				$this->_bar[$key][9][] = 'first';
			}

			if (!isset($this->_bar[$key + 1]) || $this->_bar[$key + 1][0] == 'Separator')
			{
				$this->_bar[$key][9][] = 'last';
			}
		}

		// Render each button in the toolbar.
		foreach ($this->_bar as $button)
		{
			$html[] = $this->renderButton($button);
		}

		// End toolbar div.
		$html[] = '</ul>';
		$html[] = '</div>';

		return implode("\n", $html);
	}

	/**
	 * Render a tool bar.
	 *
	 * @return  string  HTML for the toolbar.
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Render a button.
	 *
	 * @param   object  &$node  A toolbar node.
	 * @return  string
	 */
	protected function renderButton(&$node)
	{
		// Get the button type.
		$type = $node[0];

		$button = $this->loadButtonType($type);

		// Check for error.
		if ($button === false)
		{
			return trans('global.toolbar.BUTTON_NOT_DEFINED', $type);
		}
		return $button->render($node);
	}

	/**
	 * Loads a button type.
	 *
	 * @param   string   $type  Button Type
	 * @param   boolean  $new   False by default
	 * @return  object
	 */
	protected function loadButtonType($type, $new = false)
	{
		$signature = md5($type);

		if (isset($this->_buttons[$signature]) && $new === false)
		{
			return $this->_buttons[$signature];
		}

		$buttonClass = __NAMESPACE__ . '\\Toolbar\\Button\\' . $type;

		/*if (!class_exists($buttonClass))
		{
			$dirs = isset($this->_buttonPath) ? $this->_buttonPath : array();
			$file = preg_replace('/[^A-Z0-9_\.-]/i', '', str_replace('_', DIRECTORY_SEPARATOR, strtolower($type))) . '.php';

			if ($buttonFile = $this->find($dirs, $file))
			{
				include_once $buttonFile;
			}
			else
			{
				throw new \InvalidArgumentException(trans('JLIB_HTML_BUTTON_NO_LOAD', $buttonClass, $buttonFile), 500);
			}
		}*/

		if (!class_exists($buttonClass))
		{
			throw new \RuntimeException("Class $buttonClass not found.", 500);
		}

		$this->_buttons[$signature] = new $buttonClass($this);

		return $this->_buttons[$signature];
	}

	/**
	 * Searches the directory paths for a given file.
	 *
	 * @param   mixed   $paths  An path string or array of path strings to search in
	 * @param   string  $file   The file name to look for.
	 * @return  mixed   The full path and file name for the target file, or boolean false if the file is not found in any of the paths.
	 */
	/*protected function find($paths, $file)
	{
		settype($paths, 'array'); //force to array

		// Start looping through the path set
		foreach ($paths as $path)
		{
			// Get the path to the file
			$fullname = $path . DIRECTORY_SEPARATOR . $file;

			// Is the path based on a stream?
			if (strpos($path, '://') === false)
			{
				// Not a stream, so do a realpath() to avoid directory
				// traversal attempts on the local file system.
				$path = realpath($path); // needed for substr() later
				$fullname = realpath($fullname);
			}

			// The substr() check added to make sure that the realpath()
			// results in a directory registered so that
			// non-registered directories are not accessible via directory
			// traversal attempts.
			if (file_exists($fullname) && substr($fullname, 0, strlen($path)) == $path)
			{
				return $fullname;
			}
		}

		// Could not find the file in the set of paths
		return false;
	}*/

	/**
	 * Add a directory where ToolBar should search for button types in LIFO order.
	 *
	 * You may either pass a string or an array of directories.
	 *
	 * Toolbar will be searching for an element type in the same order you
	 * added them. If the parameter type cannot be found in the custom folders,
	 * it will look in __DIR__ . /toolbar/button.
	 *
	 * @param   mixed  $path  Directory or directories to search.
	 * @return  void
	 */
	/*public function addButtonPath($path)
	{
		// Just force path to array.
		settype($path, 'array');

		// Loop through the path directories.
		foreach ($path as $dir)
		{
			// No surrounding spaces allowed!
			$dir = trim($dir);

			// Add trailing separators as needed.
			if (substr($dir, -1) != DIRECTORY_SEPARATOR)
			{
				// Directory
				$dir .= DIRECTORY_SEPARATOR;
			}

			// Add to the top of the search dirs.
			array_unshift($this->_buttonPath, $dir);
		}
	}*/

	/**
	 * Writes a spacer cell.
	 *
	 * @param   string  $width  The width for the cell
	 * @return  void
	 */
	public function spacer($width = '')
	{
		$this->append('Separator', 'spacer', $width);
	}

	/**
	 * Writes a divider between menu buttons
	 *
	 * @return  void
	 */
	public function divider()
	{
		$this->append('Separator', 'divider');
	}

	/**
	 * Writes a custom option and task button for the button bar.
	 *
	 * @param   string  $task        The task to perform (picked up by the switch($task) blocks.
	 * @param   string  $icon        The image to display.
	 * @param   string  $iconOver    The image to display when moused over.
	 * @param   string  $alt         The alt text for the icon image.
	 * @param   bool    $listSelect  True if required to check that a standard list item is checked.
	 * @return  void
	 */
	public function custom($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true)
	{
		// Strip extension.
		$icon = preg_replace('#\.[^.]*$#', '', $icon);

		// Add a standard button.
		$this->append('Standard', $icon, $alt, $task, $listSelect);
	}

	/**
	 * Writes the common 'new' icon for the button bar.
	 *
	 * @param   string   $task   An override for the task.
	 * @param   string   $alt    An override for the alt text.
	 * @param   boolean  $check  True if required to check that a standard list item is checked.
	 * @return  void
	 */
	public function addNew($task = 'add', $alt = 'global.toolbar.new', $check = false)
	{
		//$this->append('Standard', 'plus', $alt, $task, $check);
		$this->append('Link', 'plus', $alt, $task, $check);
	}

	/**
	 * Writes a common 'publish' button.
	 *
	 * @param   string   $task   An override for the task.
	 * @param   string   $alt    An override for the alt text.
	 * @param   boolean  $check  True if required to check that a standard list item is checked.
	 * @return  void
	 */
	public function publish($task = 'publish', $alt = 'global.toolbar.publish', $check = false)
	{
		$this->append('Standard', 'publish', $alt, $task, $check);
	}

	/**
	 * Writes a common 'publish' button for a list of records.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 * @return  void
	 */
	public function publishList($task = 'publish', $alt = 'global.toolbar.publish')
	{
		$this->append('Standard', 'publish', $alt, $task, true);
	}

	/**
	 * Writes a common 'unpublish' button.
	 *
	 * @param   string   $task   An override for the task.
	 * @param   string   $alt    An override for the alt text.
	 * @param   boolean  $check  True if required to check that a standard list item is checked.
	 * @return  void
	 */
	public function unpublish($task = 'unpublish', $alt = 'global.toolbar.unpublish', $check = false)
	{
		$this->append('Standard', 'unpublish', $alt, $task, $check);
	}

	/**
	 * Writes a common 'unpublish' button for a list of records.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 * @return  void
	 */
	public function unpublishList($task = 'unpublish', $alt = 'global.toolbar.unpublish')
	{
		$this->append('Standard', 'unpublish', $alt, $task, true);
	}

	/**
	 * Writes a common 'delete' button for a list of records.
	 *
	 * @param   string  $msg   Postscript for the 'are you sure' message.
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 * @return  void
	 */
	public function deleteList($msg = '', $task = 'remove', $alt = 'global.toolbar.delete')
	{
		if ($msg)
		{
			$this->append('Confirm', $msg, 'trash', $alt, $task, true);
		}
		else
		{
			$this->append('Standard', 'trash', $alt, $task, true);
		}
	}

	/**
	 * Writes a save button for a given option.
	 * Apply operation leads to a save action only (does not leave edit mode).
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 * @return  void
	 */
	public function apply($task = 'apply', $alt = 'global.toolbar.save')
	{
		$this->append('Standard', 'save', $alt, $task, false);
	}

	/**
	 * Writes a save button for a given option.
	 * Save operation leads to a save and then close action.
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 * @return  void
	 */
	public function save($task = 'save', $alt = 'global.toolbar.save and close')
	{
		$this->append('Standard', 'save-close', $alt, $task, false);
	}

	/**
	 * Writes a save and create new button for a given option.
	 * Save and create operation leads to a save and then add action.
	 *
	 * @param   string  $task
	 * @param   string  $alt
	 * @return  void
	 */
	public function save2new($task = 'save2new', $alt = 'global.toolbar.save and new')
	{
		$this->append('Standard', 'save-new', $alt, $task, false);
	}

	/**
	 * Writes a save as copy button for a given option.
	 * Save as copy operation leads to a save after clearing the key,
	 * then returns user to edit mode with new key.
	 *
	 * @param   string  $task
	 * @param   string  $alt
	 * @return  void
	 */
	public function save2copy($task = 'save2copy', $alt = 'global.toolbar.save as a copy')
	{
		$this->append('Standard', 'save-copy', $alt, $task, false);
	}

	/**
	 * Writes a checkin button for a given option.
	 *
	 * @param   string   $task
	 * @param   string   $alt
	 * @param   boolean  $check  True if required to check that a standard list item is checked.
	 * @return  void
	 */
	public function checkin($task = 'checkin', $alt = 'global.toolbar.checkin', $check = true)
	{
		$this->append('Standard', 'checkin', $alt, $task, $check);
	}

	/**
	 * Writes a cancel button and invokes a cancel operation (eg a checkin).
	 *
	 * @param   string  $task  An override for the task.
	 * @param   string  $alt   An override for the alt text.
	 * @return  void
	 */
	public function cancel($task = 'cancel', $alt = 'global.toolbar.cancel')
	{
		$this->append('Standard', 'cancel', $alt, $task, false);
	}

	/**
	 * Writes a configuration button and invokes a cancel operation (eg a checkin).
	 *
	 * @param   string  $component  The name of the component, eg, com_content.
	 * @param   int     $height     The height of the popup.
	 * @param   int     $width      The width of the popup.
	 * @param   string  $alt        The name of the button.
	 * @param   string  $path       An alternative path for the configuation xml relative to PATH_ROOT.
	 * @param   string  $onClose    Called on close
	 * @return  void
	 */
	public function preferences($module, $height = 550, $width = 875, $alt = 'global.toolbar.options', $path = '', $onClose = '')
	{
		$module = urlencode($module);
		$path = urlencode($path);
		$top  = 0;
		$left = 0;

		$this->append(
			'Popup',
			'settings',//options
			$alt,
			route('admin.config.module', ['module' => $module]),
			$width,
			$height,
			$top,
			$left,
			$onClose
		);
	}

	/**
	 * Writes a button that prompts for confirmation before executing a task
	 *
	 * @param   string   $msg   Postscript for the 'are you sure' message.
	 * @param   string   $name  Name to be used as apart of the id
	 * @param   string   $task  An override for the task.
	 * @param   string   $alt   An override for the alt text.
	 * @param   boolean  $list  True to allow use of lists
	 * @return  void
	 */
	public function confirm($msg = '', $name='delete', $task = 'remove', $alt = 'global.toolbar.delete', $list = true)
	{
		$this->append('Confirm', $msg, $name, $alt, $task, $list);
	}
}
