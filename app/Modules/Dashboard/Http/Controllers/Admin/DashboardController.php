<?php

namespace App\Modules\Dashboard\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Listeners\Models\Listener;

class DashboardController extends Controller
{
	/**
	 * Display a dashboard.
	 *
	 * @return Response
	 */
	public function index()
	{
		return view('dashboard::admin.index');
	}
}
