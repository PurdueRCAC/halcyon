<?php

namespace App\Modules\Config\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Routing\Controller;

class GlobalController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return View
	 */
	public function index()
	{
		return view('config::admin.index');
	}
}
