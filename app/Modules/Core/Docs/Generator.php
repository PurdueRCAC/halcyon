<?php

namespace App\Modules\Core\Docs;

use ReflectionClass;
use phpDocumentor\Reflection\DocBlockFactory;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Cache;

/**
 * Documentation Generator Class
 */
class Generator
{
	/**
	 * Cache time in minutes
	 *
	 * A time of zero effectively means no caching
	 *
	 * @var  int
	 */
	private $cacheTime = 0;

	/**
	 * Cache file name
	 *
	 * @var  string
	 */
	private $cacheFile = 'openapi';

	/**
	 * Var to hold sections
	 *
	 * @var  array<string,array<int,string>>
	 */
	private $sections = array();

	/**
	 * Schema output
	 *
	 * @var  array<string,mixed>
	 */
	private $output = array();

	/**
	 * Create sections from module api controllers
	 *
	 * @param   int  $cacheTime  Cache time in minutes
	 * @return  void
	 */
	public function __construct(int $cacheTime = 720)
	{
		$this->cacheTime = $cacheTime * 60;
	}

	/**
	 * Return documentation
	 * 
	 * @param   string  $format  Output format
	 * @param   bool    $force  Force new version
	 * @return  string|array<string,mixed>
	 */
	public function output(string $format = 'json', bool $force = false)
	{
		$output = $this->getOutputFromCache($force)->output;

		// option to switch formats
		switch ($format)
		{
			case 'array':
				break;
			case 'php':
				$output = serialize($output);
				break;
			case 'json':
			default:
				$output = json_encode($output);
		}

		return $output;
	}

	/**
	 * Load from cache
	 */
	private function getOutputFromCache(bool $force = false): self
	{
		if ($force || $this->cacheTime == 0)
		{
			$output = $this->generate();
		}
		else
		{
			$output = Cache::remember('apidocs', $this->cacheTime, function ()
			{
				$docs = $this->generate();
				return json_encode($docs);
			});
			$output = json_decode($output, true);
		}

		$this->sections = $output;
		$this->output = $this->toOpenAPI();
		$this->saveOpenAPI($this->output);

		return $this;
	}

	/**
	 * Generate the OpenAPI documentation
	 *
	 * @return array<string,mixed>
	 */
	public function toOpenAPI(): array
	{
		$ignore = ['method', '_metadata', 'uri', 'authorization', 'param', 'return'];
		$output = array(
			'openapi' => '3.0.0',
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
			'paths' => array()
		);

		foreach ($this->sections as $name => $section)
		{
			foreach ($section as $controller => $info)
			{
				foreach ($info['endpoints'] as $endpoint)
				{
					$path = $endpoint['uri'];
					$method = strtolower($endpoint['method']);

					foreach ($ignore as $key)
					{
						if (isset($endpoint[$key]))
						{
							unset($endpoint[$key]);
						}
					}
					
					if (isset($endpoint['name']))
					{
						$endpoint['description'] = $endpoint['name'];
						unset($endpoint['name']);
					}

					if (!isset($output['paths'][$path]))
					{
						$output['paths'][$path] = array();
					}

					if (isset($endpoint['response']) || array_key_exists('response', $endpoint))
					{
						if ($endpoint['response'])
						{
							$endpoint['responses'] = $endpoint['response'];
						}
						unset($endpoint['response']);
					}

					$requestbodies = array();
					$req = array();

					foreach ($endpoint['parameters'] as $k => $param)
					{
						if (isset($param['type']) && !isset($param['schema']))
						{
							$param['schema'] = array(
								'type' => $param['type'],
							);
							unset($param['type']);
						}

						if (!isset($param['in']))
						{
							$param['in'] = 'body';
						}

						// Capture POST and PUT params
						if ($param['in'] == 'body')
						{
							$requestbodies[$param['name']] = array(
								'description' => $param['description'],
								'type' => $param['schema']['type'],
								'default' => isset($param['schema']['default']) ? $param['schema']['default'] : null
							);
							if ($param['required'])
							{
								$req[] = $param['name'];
							}

							unset($endpoint['parameters'][$k]);
							continue;
						}

						$endpoint['parameters'][$k] = $param;
					}

					if (!empty($requestbodies))
					{
						$endpoint['requestBody'] = array(
							'content' => array(
								'*/*' => array( //application/x-www-form-urlencoded
									'schema' => array(
										'type' => 'object',
										'properties' => $requestbodies,
										'required' => $req
									)
								)
							)
						);
					}

					$output['paths'][$path][$method] = $endpoint;
				}
			}
		}

		return $output;
	}

	/**
	 * Save the OpenAPI documentation to file
	 */
	public function saveOpenAPI($output): void
	{
		// create cache ffile
		$file = public_path('openapi.json');

		// save cache file
		file_put_contents($file, json_encode($output));
	}

	/**
	 * Generate Doc
	 * 
	 * @return  array<string,mixed>
	 */
	private function generate(): array
	{
		// only load sections if we dont have a cache
		$this->discoverModuleSections();

		// generate output by processing sections
		$this->sections = $this->processModuleSections($this->sections);

		return $this->sections;
	}

	/**
	 * Get all modules that have API routes
	 *
	 * @return \Illuminate\Support\Collection|\Tightenco\Collect\Support\Collection
	 */
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
	private function discoverModuleSections(): void
	{
		foreach ($this->modules() as $module)
		{
			$this->sections[$module->getLowerName()] = glob(module_path($module->getName()) . '/Http/Controllers/Api/*.php');
		}
	}

	/**
	 * Get module sections
	 *
	 * @return array<string,mixed>
	 */
	public function sections(): array
	{
		return $this->sections;
	}

	/** 
	 * Process sections
	 * 
	 * @param   array<string,array<int,string>>  $sections  All the module api controllers grouped by module
	 * @return  array<string,mixed>
	 */
	private function processModuleSections(array $sections): array
	{
		$output = array();

		// Loop through each module grouping
		foreach ($sections as $module => $files)
		{
			// If we dont have an array for that module let's create it
			if (!isset($output[$module]))
			{
				$output[$module] = [];
			}

			// Loop through each controller and get documentation
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

		return $output;
	}

	/**
	 * Process an individual file
	 * 
	 * @param   string  $file  File path
	 * @return  array<string,mixed>   Processed endpoints
	 */
	private function processFile(string $file): array
	{
		// var to hold output
		$output = array();

		$className = $this->parseClassFromFile($file);
		$module    = $this->parseClassFromFile($file, true)['module'];

		// Push file to files array
		//$this->output['files'][] = $file;

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
					'method'     => $method->getName()
				)
			);

			// Loop through each tag
			foreach ($phpdoc->getTags() as $tag)
			{
				$name    = strtolower(str_replace('api', '', $tag->getName()));
				$content = $tag->getDescription()->render();

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
	private function parseClassFromFile(string $file, bool $returnAsParts = false)
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

		// do we want to return as parts?
		if ($returnAsParts)
		{
			$parts['module']     = $parts[2];
			$parts['controller'] = end($parts);
			return $parts;
		}

		// put all the pieces back together
		return str_replace('.', '_', implode('\\', $parts));
	}
}
