<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Plugin;

use App\Halcyon\Config\Registry;
use Illuminate\Support\Fluent;

/**
 * Base class for plugins to extend
 */
class Plugin extends Fluent
{
	/**
	 * A Registry object holding the parameters for the plugin
	 *
	 * @var  Registry
	 */
	public $params = null;

	/**
	 * The name of the plugin
	 *
	 * @var  string
	 */
	protected $_name = null;

	/**
	 * The plugin type
	 *
	 * @var  string
	 */
	protected $_type = null;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var  boolean
	 */
	protected $_autoloadLanguage = false;

	/**
	 * Constructor
	 *
	 * @param   object  $subject  Event dispatcher
	 * @param   array   $config   Optional configurations to be used
	 * @return  void
	 */
	public function __construct($attributes = [])
	{
		parent::__construct($attributes);

		$r = new ReflectionClass($this);

		$cls = $r->getName();

		$segments = explode('\\', $cls);

		// Class name
		array_pop($segments);

		// Name folder
		$this->_name = array_pop($segments);

		// Type folder
		$this->_type = array_pop($segments);

		// Get the parameters.
		/*if (isset($attributes['params']))
		{
			$this->params = $attributes['params'];
		}

		if (!($this->params instanceof Registry))
		{
			$this->params = new Registry($this->params);
		}

		// Get the plugin name.
		if (isset($attributes['name']))
		{
			$this->_name = $attributes['name'];
		}

		// Get the plugin type.
		if (isset($attributes['type']))
		{
			$this->_type = $attributes['type'];
		}*/

		// Load the language files if needed.
		if ($this->_autoloadLanguage)
		{
			$this->loadLanguage();
		}
	}

	/**
	 * Loads the plugin language file
	 *
	 * @param   string   $extension  The extension for which a language file should be loaded
	 * @param   string   $basePath   The basepath to use
	 * @return  boolean  True, if the file has successfully loaded.
	 */
	protected function loadLanguage($path = '')
	{
		if (empty($path))
		{
			$path = app_path() . '/Listeners/' . $this->_type . '/' . $this->_name . '/lang';
		}

		return app('translator')->addNamespace('listeners.' . strtolower($this->_type), $path);
	}
}
