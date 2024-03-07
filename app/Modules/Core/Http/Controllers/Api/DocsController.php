<?php

namespace App\Modules\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
	 * Display API schema
	 *
	 * @apiMethod GET
	 * @apiUri    /api
	 * @param  Request $request
	 * @return JsonResponse
	 */
	public function index(Request $request): JsonResponse
	{
		$cacheTime = config('app.debug') ? 0 : config('module.core.api_cache', 720);

		$generator = new Generator($cacheTime);
		$data = $generator->output('array');

		return response()->json($data);
	}
}
