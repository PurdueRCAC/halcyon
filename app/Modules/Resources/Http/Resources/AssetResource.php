<?php

namespace App\Modules\Resources\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Resources\Events\AssetBeforeDisplay;
use App\Modules\Queues\Models\Scheduler;

class AssetResource extends JsonResource
{
	public $extended = true;

	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   Request  $request
	 * @return  array
	 */
	public function toArray(Request $request)
	{
		if ($this->extended)
		{
			event($event = new AssetBeforeDisplay($this->resource));
		}

		$data = parent::toArray($request);

		$data['children'] = $this->descendents;

		$data['priorchildren'] = $this->descendents()
			->onlyTrashed()
			->get();

		$data['subresources'] = $this->subresources
			->each(function($item, $key)
			{
				$item->api = route('api.resources.subresources.read', ['id' => $item->id]);
			});

		$data['priorsubresources'] = $this->subresources()
			->onlyTrashed()
			->each(function($item, $key)
			{
				$item->api = route('api.resources.subresources.read', ['id' => $item->id]);
			});

		$data['api'] = route('api.resources.read', ['id' => $this->id]);

		$subs = $this->subresources->pluck('id')->toArray();

		$scheduler = Scheduler::query()
			->whereIn('queuesubresourceid', $subs)
			->limit(1)
			->get()
			->first();

		if ($scheduler)
		{
			$scheduler->api = route('api.queues.schedulers.read', ['id' => $scheduler->id]);

			$data['scheduler'] = $scheduler;
		}

		// [!] Legacy compatibility
		if ($request->segment(1) == 'ws')
		{
			$data['id'] = '/ws/resource/' . $data['id'];
			$data['schedulerid'] = '/ws/scheduler/' . $data['schedulerid'];

			if (!$this->trashed())
			{
				$data['datetimeremoved'] = '0000-00-00 00:00:00';
			}
		}

		$data['facets'] = $this->facets;

		/*foreach ($this->facets as $facet)
		{
			$data['facets'][$facet->facetType->name] = $facet->value;
		}*/

		return $data;
	}
}