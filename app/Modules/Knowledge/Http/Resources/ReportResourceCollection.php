<?php

namespace App\Modules\Knowledge\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ReportResourceCollection extends ResourceCollection
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		$user = auth()->user();

		$this->collection->each(function ($item, $key) use ($user)
		{
			$item->setAttribute('api', route('api.knowledge.read', ['id' => $item->id]));
			$item->setAttribute('url', route('site.knowledge.show', ['id' => $item->id]));
			//$row->formatteddate = $row->formatDate($row->getOriginal('datetimenews'), $row->getOriginal('datetimenewsend'));
			$item->setAttribute('formattedreport', $item->formattedReport());
			$item->setAttribute('comments', $item->comments);
			$item->setAttribute('users', $item->users);
			$item->setAttribute('resources', $item->resources);

			// Permissions check
			$can = array(
				'edit'   => false,
				'delete' => false,
			);

			if ($user)
			{
				$can['edit']   = ($user->can('edit knowledge') || ($user->can('edit.own knowledge') && $item->userid == $user->id));
				$can['delete'] = $user->can('delete knowledge');
			}

			$item->setAttribute('can', $can);
		});

		return parent::toArray($request);
	}
}