<?php

namespace App\Modules\Core\Entities;

use Illuminate\Support\Fluent;
use Illuminate\Support\Facades\DB;

/**
 * Model class for getting system information
 */
class KnowItAll extends Fluent
{
	/**
	 * @var  array  some php settings
	 */
	protected $php_settings = null;

	/**
	 * @var  array config values
	 */
	protected $config = null;

	/**
	 * @var  array  somme system values
	 */
	protected $info = null;

	/**
	 * @var  string  php info
	 */
	protected $php_info = null;

	/**
	 * @var  array  informations about writable state of directories
	 */
	protected $directories = null;

	/**
	 * @var  string  The current editor.
	 */
	protected $editor = null;

	/**
	 * Method to get the ChangeLog
	 *
	 * @return  array  some php settings
	 */
	public function getPhpSettings()
	{
		if (is_null($this->php_settings))
		{
			$this->php_settings = array();
			$this->php_settings['safe_mode']          = ini_get('safe_mode') == '1';
			$this->php_settings['display_errors']     = ini_get('display_errors') == '1';
			$this->php_settings['short_open_tag']     = ini_get('short_open_tag') == '1';
			$this->php_settings['file_uploads']       = ini_get('file_uploads') == '1';
			$this->php_settings['magic_quotes_gpc']   = ini_get('magic_quotes_gpc') == '1';
			$this->php_settings['register_globals']   = ini_get('register_globals') == '1';
			$this->php_settings['output_buffering']   = (bool) ini_get('output_buffering');
			$this->php_settings['open_basedir']       = ini_get('open_basedir');
			$this->php_settings['session.save_path']  = ini_get('session.save_path');
			$this->php_settings['session.auto_start'] = ini_get('session.auto_start');
			$this->php_settings['disable_functions']  = ini_get('disable_functions');
			$this->php_settings['xml']                = extension_loaded('xml');
			$this->php_settings['zlib']               = extension_loaded('zlib');
			$this->php_settings['zip']                = function_exists('zip_open') && function_exists('zip_read');
			$this->php_settings['mbstring']           = extension_loaded('mbstring');
			$this->php_settings['iconv']              = function_exists('iconv');
		}
		return $this->php_settings;
	}

	/**
	 * Method to get the config
	 *
	 * @return  array  Config values
	 */
	public function getConfig()
	{
		if (is_null($this->config))
		{
			$config = config();

			/*foreach (array('components', 'plugins', 'templates') as $ignore)
			{
				if (isset($config[$ignore]))
				{
					unset($config[$ignore]);
				}
			}*/

			$this->config = array();

			$blur = array('host', 'user', 'password', 'ftp_user', 'ftp_pass', 'smtpuser', 'smtppass', 'secret');

			foreach ($config as $section => $data)
			{
				if (is_array($data))
				{
					foreach ($data as $key => $value)
					{
						if (in_array($key, $blur)
						 || substr($key, -strlen('password')) == 'password'
						 || substr($key, -strlen('secret')) == 'secret')
						{
							$value = 'xxxxxx';
						}

						$this->config[$section . '.' . $key] = $value;
					}
				}
				else
				{
					if (in_array($data, $blur)
					 || substr($data, -strlen('password')) == 'password'
					 || substr($data, -strlen('secret')) == 'secret')
					{
						$value = 'xxxxxx';
					}

					$this->config[$section] = $value;
				}
			}
		}
		return $this->config;
	}

	/**
	 * Method to get the system information
	 *
	 * @return  array  System information values
	 */
	public function getInfo()
	{
		if (is_null($this->info))
		{
			if (isset($_SERVER['SERVER_SOFTWARE']))
			{
				$sf = $_SERVER['SERVER_SOFTWARE'];
			}
			else
			{
				$sf = getenv('SERVER_SOFTWARE');
			}

			$this->info = array();

			$results = DB::select(DB::raw("SELECT version() AS ver"));
			$this->info['dbversion'] = $results[0]->ver;
			$this->info['php']         = php_uname();
			//$this->info['dbversion']   = $db->getVersion();
			//$this->info['dbcollation'] = $db->getCollation();
			$this->info['phpversion']  = phpversion();
			$this->info['server']      = $sf;
			$this->info['sapi_name']   = php_sapi_name();
			$this->info['version']     = '0.1';
			$this->info['platform']    = 'Halcyon';
			$this->info['useragent']   = $_SERVER['HTTP_USER_AGENT'];
		}
		return $this->info;
	}

	/**
	 * Method to get the PHP info
	 *
	 * @return  string  PHP info
	 */
	public function getPhpInfo()
	{
		if (is_null($this->php_info))
		{
			ob_start();
			date_default_timezone_set('UTC');
			phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES);
			$phpinfo = ob_get_contents();
			ob_end_clean();

			preg_match_all('#<body[^>]*>(.*)</body>#siU', $phpinfo, $output);
			$output = preg_replace('#<table[^>]*>#', '<table class="table table-hover adminlist">', $output[1][0]);
			$output = preg_replace('#(\w),(\w)#', '\1, \2', $output);
			$output = preg_replace('#<hr />#', '', $output);
			$output = str_replace('<div class="center">', '', $output);
			$output = preg_replace('#<tr class="h">(.*)<\/tr>#', '<thead><tr class="h">$1</tr></thead><tbody>', $output);
			$output = str_replace('</table>', '</tbody></table>', $output);
			$output = str_replace('</div>', '', $output);

			$this->php_info = $output;
		}
		return $this->php_info;
	}

	/**
	 * Method to get the directory states
	 *
	 * @return  array  states of directories
	 */
	public function getDirectory()
	{
		if (is_null($this->directories))
		{
			$this->directories = array();

			$this->_addDirectory(public_path('modules'), public_path('modules'));
			$this->_addDirectory(public_path('themes'), public_path('themes'));
			$this->_addDirectory(public_path('listeners'), public_path('listeners'));
			//$this->_addDirectory(public_path('widgets'), public_path('widgets'));
			$this->_addDirectory(storage_path(), storage_path());
		}
		return $this->directories;
	}

	/**
	 * Add a directory to the list
	 *
	 * @param   string  $name
	 * @param   string  $path
	 * @param   string  $message
	 * @return  void
	 */
	private function _addDirectory($name, $path, $message = '')
	{
		$name = str_replace(base_path(), '', $path);

		$this->directories[$name] = array(
			'writable' => is_writable($path),
			'message'  => $message
		);
	}

	/**
	 * Method to get the editor
	 * has to be removed (it is present in the config...)
	 *
	 * @return  string  The default editor
	 */
	public function getEditor()
	{
		if (is_null($this->editor))
		{
			$this->editor = config('app.editor', 'none');
		}
		return $this->editor;
	}
}
