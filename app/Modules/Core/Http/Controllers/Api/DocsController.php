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
	 * @return Response
	 */
	public function index(Request $request)
	{
		/*$modules = collect(Module::all());
		$modules = $modules->filter(function($value, $key)
		{
			$apiRoutes = $value->getPath() . '/Http/apiRoutes.php';

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
		$generator = new Generator(config('module.core.api_chache', false));
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

		foreach ($docs['sections'] as $name => $section)
		{
			foreach ($section as $controller => $info)
			{
				foreach ($info['endpoints'] as $endpoint)
				{
					$path = $endpoint['uri'];
					$method = strtolower($endpoint['method']);

					unset($endpoint['method']);
					unset($endpoint['_metadata']);
					unset($endpoint['uri']);

					if (!isset($data['paths'][$path]))
					{
						$data['paths'][$path] = array();
					}

					foreach ($endpoint['parameters'] as $k => $param)
					{
						if (isset($param['type']) && !isset($param['schema']))
						{
							$param['schema'] = array(
								'type' => $param['type'],
							);
							unset($param['type']);
						}

						$endpoint['parameters'][$k] = $param;
					}

					/*if (isset($endpoint['parameters']))
					{
						$data['paths'][$path]['parameters'] = $endpoint['parameters'];
						unset($endpoint['parameters']);
					}*/

					$data['paths'][$path][$method] = $endpoint;
				}
			}
		}

		return response()->json($data);
	}
}
