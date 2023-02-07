<?php

namespace App\Modules\Core\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use App\Modules\Core\Docs\Generator;
use Nwidart\Modules\Facades\Module;

class DocsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param   Request  $request
	 * @return  View
	 */
	public function index(Request $request): View
	{
		$modules = collect(Module::all());
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
		}

		// Generate documentation
		$generator = new Generator(config('app.debug') ? false : config('module.core.api_chache', 720));
		$documentation = $generator->output('array');

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
