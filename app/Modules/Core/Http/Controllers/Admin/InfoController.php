<?php

namespace App\Modules\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Core\Entities\KnowItAll;

class InfoController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param   Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		$model = new KnowItAll();

		return view('core::admin.info.index', [
			'model' => $model,
		]);
	}

	/**
	 * Display a listing of the resource.
	 * 
	 * @param   Request  $request
	 * @return Response
	 */
	public function styles(Request $request)
	{
		return view('core::admin.styles');
	}
}
