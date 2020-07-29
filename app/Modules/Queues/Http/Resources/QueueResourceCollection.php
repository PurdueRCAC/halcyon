<?php

namespace App\Modules\Queues\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class QueueResourceCollection extends ResourceCollection
{
	/**
	 * Transform the queue collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		/*$user = auth()->user();

		$this->collection->each(function ($item, $key) use ($user)
		{
			$item->setAttribute('api', route('api.queues.read', ['id' => $item->id]));
			//$item->setAttribute('url', route('site.contactreports.show', ['id' => $item->id]));
			//$row->formatteddate = $row->formatDate($row->getOriginal('datetimenews'), $row->getOriginal('datetimenewsend'));
			//$item->setAttribute('resource', $item->resource);
			//$item->setAttribute('scheduler_policy', $item->scheduler_policy);
			//$item->setAttribute('scheduler', $item->scheduler);
			//$item->setAttribute('sizes', $item->sizes);

			// Permissions check
			$can = array(
				'edit'   => false,
				'delete' => false,
			);

			if ($user)
			{
				$can['edit']   = ($user->can('edit queues') || ($user->can('edit.own queues') && $item->userid == $user->id));
				$can['delete'] = $user->can('delete queues');
			}

			$item->setAttribute('can', $can);
		});*/

		return parent::toArray($request);
	}
}