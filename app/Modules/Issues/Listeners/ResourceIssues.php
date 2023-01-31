<?php

namespace App\Modules\Issues\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Resources\Events\AssetDisplaying;
use App\Modules\Issues\Models\Issue;
use App\Modules\Issues\Models\Issueresource;

/**
 * Resources listener
 */
class ResourceIssues
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(AssetDisplaying::class, self::class . '@handleAssetDisplaying');
	}

	/**
	 * Add a list of issues for a resource
	 *
	 * @param   AssetDisplaying $event
	 * @return  void
	 */
	public function handleAssetDisplaying(AssetDisplaying $event): void
	{
		if (!app()->has('isAdmin') || !app()->get('isAdmin'))
		{
			return;
		}

		$i = (new Issue)->getTable();
		$r = (new Issueresource)->getTable();

		$issues = Issue::query()
			->select($i . '.*')
			->withCount('comments')
			->join($r, $r . '.issueid', $i . '.id')
			->where($r . '.resourceid', '=', $event->getAsset()->id)
			->orderBy('id', 'desc')
			->paginate();

		if (count($issues))
		{
			$event->addSection(
				'issues',//route('admin.resources.edit', ['id' => $event->getAsset()->id]),
				trans('issues::issues.issues'),
				false,
				view('issues::admin.issues.asset', [
					'rows' => $issues
				])
			);
		}
	}
}
