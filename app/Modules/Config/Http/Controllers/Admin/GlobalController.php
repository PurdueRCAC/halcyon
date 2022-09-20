<?php

namespace App\Modules\Config\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class GlobalController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		return view('config::admin.index');
	}
}
