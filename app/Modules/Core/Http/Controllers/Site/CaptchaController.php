<?php

namespace App\Modules\Core\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class CaptchaController extends Controller
{
	/**
	 * Display a captcha
	 * 
	 * @param  Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		if (ob_get_contents())
		{
			ob_clean();
		}

		captcha(true);
	}
}
