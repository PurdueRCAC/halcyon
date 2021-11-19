<?php

namespace App\Modules\Core\Docs;

use ReflectionClass;
use phpDocumentor\Reflection\DocBlockFactory;
use Nwidart\Modules\Facades\Module;

/**
 * Documentation Generator Class
 */
class Generator
{
	/**
	 * Cache results?
	 * 
	 * @var  bool
	 */
	private $cache = true;

	/**
	 * Var to hold sections
	 * 
	 * @var  array
	 */
	private $sections = array();

	/**
	 * Var to hold output
	 * 
	 * @var  array
	 */
	private $output = array();

	/**
	 * Create sections from module api controllers
	 *
	 * @param   bool  $cache  Cache results?
	 * @return  void
	 */
	public function __construct($cache = true)
	{
		$this->cache = (bool) $cache;

		// create all needed keys in output
		$this->output = array(
			'openapi'  => '3.0.0',
			'info' => array(
				'title' => trans('core::docs.api title', ['title' => config('app.name')]),
				//'description' => trans('core::docs.api description'),
				'version' => '0.0.1',
			),
			'servers' => array(
				array(
					'url' => route('api.core.index'),
					'description' => trans('core::docs.api server')
				),
			),
			'components' => array(
				'parameters' => array(
					'limitParam' => array(
						'name' => 'limit',
						'in' => 'query',
						'description' => 'Number of records to return per page',
						'required' => false,
						'schema' => array(
							'type' => 'integer',
							'format' => 'int32'
						),
					),
					'pageParam' => array(
						'name' => 'page',
						'in' => 'query',
						'description' => '',
						'required' => false,
						'schema' => array(
							'type' => 'integer',
							'format' => 'int32'
						),
					),
				),
				'securitySchemes' => array(
					'api_token' => array(
						'type' => 'apiKey',
						'name' => 'api_token',
						'in'   => 'header'
					),
				),
			),
			'sections' => array(),
			'versions' => array(
				'max'       => '',
				'available' => array()
			),
			'errors'   => array(),
			'files'    => array()
		);
	}

	/**
	 * Return documentation
	 * 
	 * @param   string  $format  Output format
	 * @param   bool    $format  Force new version
	 * @return  string
	 */
	public function output($format = 'json', $force = false)
	{
		// generate output
		if ($force || !$this->cache())
		{
			$this->generate();
		}

		// option to switch formats
		switch ($format)
		{
			case 'array':
				break;
			case 'php':
				$this->output = serialize($this->output);
				break;
			case 'json':
			default:
				$this->output = json_encode($this->output);
		}

		return $this->output;
	}

	/**
	 * Load from cache
	 * 
	 * @return  boolean
	 */
	private function cache()
	{
		if (!$this->cache)
		{
			return false;
		}

		// Get developer params to get cache expiration
		$cacheExpiration = config()->get('module.core.doc_expiration', 720);

		// Check if we have a cache file
		$cacheFile = storage_path('app/public/openapi.json');

		if (file_exists($cacheFile))
		{
			// Check if its still valid
			$cacheMakeTime = @filemtime($cacheFile);

			if (time() - $cacheExpiration < $cacheMakeTime)
			{
				$this->output = json_decode(file_get_contents($cacheFile), true);
				return true;
			}
		}

		return false;
	}

	/**
	 * Generate Doc
	 * 
	 * @return  void
	 */
	private function generate()
	{
		// only load sections if we dont have a cache
		$this->discoverModuleSections();

		// generate output by processing sections
		$this->output['sections'] = $this->processModuleSections($this->sections);

		// remove duplicate available versions & order
		$this->output['versions']['available'] = array_unique($this->output['versions']['available']);

		// get the highest version available
		$this->output['versions']['max'] = end($this->output['versions']['available']);

		// create cache folder
		$cacheFile = storage_path('app/public/openapi.json');

		/*if (!app('filesystem')->exists(dirname($cacheFile)))
		{
			app('filesystem')->makeDirectory(dirname($cacheFile));
		}*/

		// save cache file
		file_put_contents($cacheFile, json_encode($this->output));
	}

	public function modules()
	{
		$modules = collect(Module::all());
		$modules = $modules->filter(function($value, $key)
		{
			$apiRoutes = $value->getPath() . '/Routes/api.php';

			return file_exists($apiRoutes) && $value->isStatus(true);
		});

		return $modules;
	}

	/**
	 * Load api controller files and group by module
	 * 
	 * @return  void
	 */
	private function discoverModuleSections()
	{
		foreach ($this->modules() as $module)
		{
			$this->sections[$module->getLowerName()] = glob(module_path($module->getName()) . '/Http/Controllers/Api/*.php');
		}
	}

	/** 
	 * Process sections
	 * 
	 * @param   array  $sections  All the module api controllers grouped by module
	 * @return  array
	 */
	private function processModuleSections($sections)
	{
		// var to hold output
		$output = array();

		// loop through each module grouping
		foreach ($sections as $module => $files)
		{
			// if we dont have an array for that module let's create it
			if (!isset($output[$module]))
			{
				$output[$module] = [];
			}

			// loop through each file
			foreach ($files as $file)
			{
				if (!preg_match('/(.*)Controller.php$/', $file))
				{
					continue;
				}

				$controller = str_replace('.php', '', basename($file));
				$controller = preg_replace('/(.*)Controller$/', "$1", $controller);

				$data = $this->processFile($file);

				if (!empty($data) && !empty($data['endpoints']))
				{
					$output[$module][$controller] = $data;
				}
			}

			ksort($output[$module]);
		}

		// return output
		return $output;
	}

	/**
	 * Process an individual file
	 * 
	 * @param   string  $file  File path
	 * @return  array   Processed endpoints
	 */
	private function processFile($file)
	{
		// var to hold output
		$output = array();

		//require_once $file;

		$className = $this->parseClassFromFile($file);
		$module    = $this->parseClassFromFile($file, true)['module'];
		//$version   = $this->parseClassFromFile($file, true)['version'];

		// Push file to files array
		$this->output['files'][] = $file;

		// Push version to versions array
		//$this->output['versions']['available'][] = $version;

		/*if (!class_exists($className))
		{
			return $output;
		}*/

		$controller = basename($file);
		$controller = preg_replace('/\.[^.]*$/', '', $controller);
		$controller = preg_replace('/(.*)Controller$/', "$1", $controller);

		$classReflector = new ReflectionClass($className);
		$docblock = DocBlockFactory::createInstance();

		$output = [
			'module'      => $module,
			'controller'  => $controller,
			'name'        => '',
			'description' => '',
			'endpoints'   => [],
		];

		if ($doc = $classReflector->getDocComment())
		{
			$phpdoc = $docblock->create($doc);

			$output['name'] = $phpdoc->getSummary();
			$output['description'] = $phpdoc->getDescription()->render();
		}

		foreach ($classReflector->getMethods() as $method)
		{
			// Create docblock object & make sure we have something
			//$phpdoc = new DocBlock($method);
			$phpdoc = $docblock->create($method->getDocComment());

			// Skip method in the parent class (already processed), 
			if ($className != $method->getDeclaringClass()->getName())
			{
				continue;
			}

			// Skip if we dont have a short desc
			// but put in error
			if (!$phpdoc->getSummary())
			{
				$this->output['errors'][] = sprintf('Missing docblock for method "%s" in "%s"', $method->getName(), $file);
				continue;
			}

			
			//$parts = explode('v', $controller);
			//$v = array_pop($parts);
			//$controller = implode('v', $parts);
			$skip = false;

			// Create endpoint data array
			$endpoint = array(
				//'name'        => substr($method->getName(), 0, -4),
				//'description' => preg_replace('/\s+/', ' ', $phpdoc->getShortDescription()), // $phpdoc->getLongDescription()->getContents()
				'name'        => $phpdoc->getSummary(),
				'description' => $phpdoc->getDescription()->render(), //->getContents(),
				'method'      => '',
				'uri'         => '',
				'parameters'  => array(),
				'response'    => null,
				'_metadata'   => array(
					'controller' => $controller,
					'module'     => $module,
					//'version'    => $version,
					'method'     => $method->getName()
				)
			);

			// Loop through each tag
			foreach ($phpdoc->getTags() as $tag)
			{
				$name    = strtolower(str_replace('api', '', $tag->getName()));
				$content = $tag->getDescription()->render(); //Content();

				if ($name == 'skip')
				{
					$skip = true;
				}

				// Handle parameters separately
				// json decode param input
				if ($name == 'parameter')
				{
					$parameter = json_decode($content, true);

					if (json_last_error() != JSON_ERROR_NONE)
					{
						$this->output['errors'][] = sprintf('Unable to parse parameter info for method "%s" in "%s"', $method->getName(), $file);
						continue;
					}

					if (!isset($parameter['schema']))
					{
						$parameter['schema'] = array('type' => 'string');
					}

					$endpoint['parameters'][] = (array) $parameter;
					continue;
				}

				if ($name == 'uri')
				{
					$content = str_replace(['{module}', '{controller}'], [$module, $controller], $content);

					if ($controller == $module)
					{
						$content = str_replace($module . '/' . $controller, $module, $content);
					}
				}

				if ($name == 'response')
				{
					$response = json_decode($content);

					if (json_last_error() != JSON_ERROR_NONE)
					{
						echo sprintf('Unable to parse response info for method "%s" in "%s"', $method->getName(), $file);
						$this->output['errors'][] = sprintf('Unable to parse response info for method "%s" in "%s"', $method->getName(), $file);
						continue;
					}

					$endpoint['response'] = $response;
					continue;
				}

				/*if ($name == 'uri' && $method->getName() == 'index')
				{
					$content .= $module;
				}*/

				// Add data to endpoint data
				$endpoint[$name] = $content;
			}

			if ($skip)
			{
				continue;
			}

			// Add endpoint to output
			// We always want index to be first in the list
			if ($method->getName() == 'index')
			{
				array_unshift($output['endpoints'], $endpoint);
			}
			else
			{
				$output['endpoints'][] = $endpoint;
			}
		}

		return $output;
	}

	/**
	 * Get class name based on file
	 * 
	 * @param   string  $file           File path
	 * @param   bool    $returnAsParts  Return as parts?
	 * @return  mixed
	 */
	private function parseClassFromFile($file, $returnAsParts = false)
	{
		// replace some values in file path to get what we need
		$file = str_replace(
			array(
				app_path(),
				'.php'
			),
			array('', ''),
			$file
		);

		// split by "/"
		$parts = explode('/', 'App' . $file);
		//array_unshift($parts, 'Modules');

		// do we want to return as parts?
		if ($returnAsParts)
		{
			//$parts['namespace']  = $parts[0];
			$parts['module']     = $parts[2];
			//$parts['client']     = $parts[2];
			$parts['controller'] = end($parts); //$parts[4];
			//$b = explode('v', $parts[4]);
			//$parts['version']    = end($b);//$parts[4];
			return $parts;
		}

		// capitalize first letter
		//$parts = array_map('ucfirst', $parts);

		// put all the pieces back together
		return str_replace('.', '_', implode('\\', $parts));
	}
}
