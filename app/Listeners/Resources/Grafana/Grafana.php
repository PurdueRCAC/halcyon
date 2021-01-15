<?php
namespace App\Listeners\Resources\Grafana;

use Illuminate\Support\Facades\Storage;
use App\Modules\Status\Events\StatusRetrieval;
use App\Modules\Resources\Entities\Asset;
use GuzzleHttp\Client;
use Carbon\Carbon;

/**
 * Grafana listener for Resources
 */
class Grafana
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(StatusRetrieval::class, self::class . '@handleStatusRetrieval');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   StatusRetrieval  $event
	 * @return  void
	 */
	public function handleStatusRetrieval(StatusRetrieval $event)
	{
		$resource = $event->asset;

		$url = $resource->params->get('monitor');
		$url = $resource->rolename == 'hammer' ? 'http://grafana.hammer.rcac.purdue.edu:9090' : $url;
		$url = $resource->rolename == 'bell' ? 'http://grafana.bell.rcac.purdue.edu:9090' : $url;

		$url = 'http://grafana.' . $resource->rolename . '.rcac.purdue.edu:9090';

		if (!$url)
		{
			return;
		}

		$retrieve = true;
		$cache = storage_path('app/status_' . $resource->rolename . '.json');
		$hourago = Carbon::now()->modify('-1 hour')->timestamp;

		if (file_exists($cache))
		{
			$lastmodified = Storage::lastModified($cache);

			if ($lastmodified > $hourago)
			{
				$retrieve = false;

				$data = json_decode(file_get_contents($path), true);
				$resource->statusUpdate = Carbon::parse($lastmodified);
			}
		}

		if ($retrieve)
		{
			try
			{
				$client = new Client();

				$checks = [
					// Frontends
					'frontends' => '/api/v1/query?query=frontend_status_check_sum&time=1610710613',
					// Jupyterhub
					'jupyterhub' => '/api/v1/query?query=jupyterhub_status&time=1610710848',
					// Thinlinc
					'thinlinc' => '/api/v1/query?query=thinlinc_status&time=1610710848',
					// SSH
					'ssh' => '/api/v1/query?query=sum(frontend_status_check_count)&time=1610710848',
				];

				$data = array();

				foreach ($checks as $check => $endpoint)
				{
					$res = $client->request('GET', $url . $endpoint);

					//$status = $res->getStatusCode();

					if ($res->getStatusCode() >= 400)
					{
						//echo $endpoint . ' - ' . $res->getStatusCode() . '<br />';
						continue;
					}

					$response = json_decode($res->getBody()->getContents());

					// Endpoint result example:
					// {
					//   "status": "success",
					//   "data": {
					//     "resultType": "vector",
					//     "result": [
					//       {
					//         "metric": {},
					//         "value": [
					//           1610710848,
					//           "0"
					//         ]
					//       }
					//     ]
					//   }
					// }
					if ($response->status == 'success' && !empty($response->data->result))
					{
						$data[$check] = array();
						$data[$check] = $response->data->result;

						/*foreach ($response->data->result as $res)
						{
							$metrics = array();

							if (isset($res->metric))
							{
								$metrics = $res->metric;
							}

							foreach ($res->value as $value)
							{
								$data[$check][] = array(
									'label' => $check,
									'value' => $value[1],
									'metrics' => $metrics,
								);
							}
						}*/
					}
				}

				$resource->statusUpdate = Carbon::now();

				//Storage::put($cache, json_encode($data));
			}
			catch (\Exception $e)
			{
			}
		}
		//echo '<pre>';print_r($data);echo '</pre>';
		$resource->data = $data;

		$impaired = 0;
		$total = 0;
		foreach ($resource->data as $section => $items)
		{
			foreach ($items as $item)
			{
				$total++;
				if ($item->value[1] == 1)
				{
					$impaired++;
				}
			}
		}

		if ($impaired)
		{
			$resource->status = 'impaired';
			if ($impaired == $total)
			{
				$resource->status = 'down';
			}
		}

		$event->asset = $resource;
	}
}
