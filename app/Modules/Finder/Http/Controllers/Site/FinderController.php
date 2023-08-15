<?php

namespace App\Modules\Finder\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class FinderController extends Controller
{
	/**
	 * Display finder
	 *
	 * @param   Request $request
	 * @return  View
	 */
	public function index(Request $request)
	{
		return view('finder::site.index');
	}
}
