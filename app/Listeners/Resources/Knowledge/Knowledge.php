<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Resources\Knowledge;

use App\Modules\Resources\Events\AssetDisplaying;
use App\Modules\Knowledge\Models\Page;

/**
 * Content listener for Resources
 */
class Knowledge
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
		//$content = $event->getBody();

		$access = [1];

		if (auth()->user())
		{
			$access = auth()->user()->getAuthorisedViewLevels();
		}

		$page = Page::query()
			->where('alias', '=', $event->getAsset()->listname)
			->where('state', '=', 1)
			->where('snippet', '=', 0)
			->whereIn('access', $access)
			->orderBy('id', 'asc')
			->get()
			->first();

		if (!$page)
		{
			return;
		}

		$overview = $page->children()
			->where('alias', '=', 'overview')
			->whereIn('access', $access)
			->get()
			->first();

		if ($overview)
		{
			$overview->variables->merge($page->variables);

			$event->addSection(
				route('site.knowledge.page', ['uri' => $page->alias]),
				$page->headline,
				true,
				$overview->body
			);

			/*$event->addSection(
				route('site.knowledge.page', ['uri' => $page->alias . '/' . $overview->alias]),
				$overview->headline,
				true,
				$overview->body
			);*/
		}
		else
		{
			$event->addSection(
				route('site.knowledge.page', ['uri' => $page->path]),
				$page->headline,
				true,
				$page->body
			);
		}

		// FAQ page
		$faq = $page->children()
			->where('alias', '=', 'faq')
			->whereIn('access', $access)
			->get()
			->first();

		if ($faq)
		{
			$faq->variables->merge($page->variables);

			$event->addSection(
				route('site.knowledge.page', ['uri' => $page->alias . '/' . $faq->alias]),
				$faq->headline
			);
		}

		// Bio
		$bio = $page->children()
			->where('alias', '=', 'bio')
			->whereIn('access', $access)
			->get()
			->first();

		if ($bio)
		{
			$bio->variables->merge($page->variables);

			$event->addSection(
				route('site.knowledge.page', ['uri' => $page->alias . '/' . $bio->alias]),
				$bio->headline
			);
		}
	}
}
