<?php

namespace App\Modules\Issues\Listeners;

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
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(AssetDisplaying::class, self::class . '@handleAssetDisplaying');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
	 * @return  void
	 */
	public function handleAssetDisplaying(AssetDisplaying $event)
	{
		if (!app()->has('isAdmin') || !app()->has('isAdmin'))
		{
			return;
		}

		$i = (new Issue)->getTable();
		$r = (new Issueresource)->getTable();

		$issues = Issue::query()
			->select($i . '.*')
			->join($r, $r . '.issueid', $i . '.id')
			->withTrashed()
			->whereIsActive()
			->where($r . '.resourceid', '=', $event->getAsset()->id)
			->get();

		//$event->getAsset()->issues = $issues;

		if (count($issues))
		{
			$event->addSection(
				route('admin.resources.edit', ['id' => $event->getAsset()->id]),
				trans('issues::issues.issues'),
				false,
				''
			);
		}
	}
}
