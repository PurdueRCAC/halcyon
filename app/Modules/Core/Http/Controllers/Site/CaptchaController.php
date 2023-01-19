<?php

namespace App\Modules\Core\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CaptchaController extends Controller
{
	/**
	 * Display a captcha
	 * 
	 * @param  Request  $request
	 * @return void
	 */
	public function index(Request $request)
	{
		captcha(true);
	}
}
