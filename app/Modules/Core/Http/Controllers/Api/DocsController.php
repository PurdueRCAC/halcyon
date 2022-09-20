<?php

namespace App\Modules\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Core\Docs\Generator;
use Nwidart\Modules\Facades\Module;

/**
 * Documentation
 *
 * @apiUri    /api
 */
class DocsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api
	 * @param   Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		/*$modules = collect(Module::all());
		$modules = $modules->filter(function($value, $key)
		{
			$apiRoutes = $value->getPath() . '/Routes/api.php';

			return file_exists($apiRoutes) && $value->isStatus(true);
		});

		$segments = $request->segments();

		// Remove 'api'
		array_shift($segments);

		// Active module
		$module = null;
		$controller = null;

		if (!empty($segments))
		{
			$module = array_shift($segments);

			if (!empty($segments))
			{
				$controller = array_shift($segments);
			}
		}*/

		// generate documentation
		$generator = new Generator((bool)config('module.core.api_chache', false));
		$docs = $generator->output('array');

		$data = array();
		$data['openapi'] = '3.0.0';
		$data['info'] = array(
			'title' => trans('core::docs.api title', ['title' => config('app.name')]),
			//'description' => trans('core::docs.api description'),
			'version' => '0.0.1',
		);
		$data['servers'] = array(
			array(
				'url' => route('api.core.index'),
				'description' => trans('core::docs.api server')
			),
		);
		$data['components'] = array(
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
		);
		$data['paths'] = array();

		$ignore = ['method', '_metadata', 'uri', 'authorization', 'param', 'return'];

		foreach ($docs['sections'] as $name => $section)
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

					if (!isset($data['paths'][$path]))
					{
						$data['paths'][$path] = array();
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

					$data['paths'][$path][$method] = $endpoint;
				}
			}
		}

		return response()->json($data);
	}
}
