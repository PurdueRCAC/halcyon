<?php

namespace App\Modules\Finder\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class FinderController extends Controller
{
	/**
	 * Display finder
	 *
	 * @return  Response
	 */
	public function index(Request $request)
	{
		return view('finder::site.index');
	}
}
