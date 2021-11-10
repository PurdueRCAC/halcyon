<?php

namespace App\Modules\Status\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Resources\Models\Type as AssetType;
use App\Modules\News\Models\Type as NewsType;

class StatusController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param   Request  $request
	 * @return  Response
	 */
	public function index(Request $request)
	{
		$restypes = AssetType::query()
			->get();

		$type = NewsType::find(1);

		if (!$type)
		{
			$type = NewsType::query()
				->whereLike('name', 'outage')
				->first();
		}

		return view('status::site.index', [
			'restypes' => $restypes,
			'type' => $type
		]);
	}
}
