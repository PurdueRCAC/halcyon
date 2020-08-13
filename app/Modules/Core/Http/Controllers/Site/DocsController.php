<?php

namespace App\Modules\Core\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Core\Docs\Generator;
use Nwidart\Modules\Facades\Module;

class DocsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index(Request $request)
	{
		$modules = collect(Module::all());
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
		}

		// generate documentation
		$generator = new Generator(config('module.core.api_chache', false));
		$documentation = $generator->output('array');

/*echo '<pre>';
print_r($documentation);
echo '</pre>';*/
		return view('core::site.docs.index', [
			'modules' => $modules,
			'module' => $module,
			'controller' => $controller,
			//'active' => $active,
			'segments' => $segments,
			'documentation' => $documentation,
		]);
	}
}
