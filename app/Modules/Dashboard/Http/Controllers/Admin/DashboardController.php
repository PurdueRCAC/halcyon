<?php

namespace App\Modules\Dashboard\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
	/**
	 * Display a dashboard.
	 *
	 * @return View
	 */
	public function index()
	{
		return view('dashboard::admin.index');
	}
}
