<?php

namespace App\Modules\Status\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\Resources\Entities\Type as AssetType;
use App\Modules\News\Models\Type as NewsType;
use GuzzleHttp\Client;

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

		/*$resources = $restype->resources()
			->whereIsActive()
			->where('listname', '!=', '')
			->where('display', '>', 0)
			->orderBy('name', 'asc')
			->get();

		foreach ($resources as $resource)
		{
			$resource->status = $resource->status ?: 'operational';

			if ($url = $resource->params->get('monitor'))
			{
				$url = $resource->rolename == 'hammer' ? 'https://grafana.hammer.rcac.purdue.edu:3000/api/dashboards/uid/QRO3OoiGz/' : $url;

				$client = new Client();
				$res = $client->request('GET', $url);

				$status = $res->getStatusCode();
				if ($res->getStatusCode() >= 400)
				{
					continue;
				}

				$resource->data = json_decode($res->getBody()->getContents());
			}
		}*/

		$type = NewsType::find(1);

		return view('status::site.index', [
			'restypes' => $restypes,
			'type' => $type
		]);
	}
}
