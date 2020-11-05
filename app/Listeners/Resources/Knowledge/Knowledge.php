<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Resources\Knowledge;

use App\Modules\Resources\Events\AssetDisplaying;
use App\Modules\Knowledge\Models\Page;
use App\Modules\Knowledge\Models\Associations;
use App\Modules\Knowledge\Models\Association;

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

		/*$page = Page::query()
			->where($p . '.alias', '=', $event->getAsset()->listname)
			->where($a . '.state', '=', 1)
			->where($p . '.snippet', '=', 0)
			->whereIn($a . '.access', $access)
			->orderBy('id', 'asc')
			->get()
			->first();*/
		$a = (new Associations)->getTable();
		$p = (new Page)->getTable();

		$assoc = Associations::query()
			->select($a . '.*')
			->join($p, $p . '.id', $a . '.page_id')
			->where($a . '.path', '=', $event->getAsset()->listname)
			->where($p . '.state', '=', 1)
			->whereIn($p . '.access', $access)
			->orderBy($a . '.id', 'asc')
			->get()
			->first();
		$page = $assoc->page;

		if (!$page)
		{
			return;
		}

		$overview = $page->children()
			->where('alias', '=', 'overview')
			->whereIn($p . '.access', $access)
			//->whereIn($a . '.access', $access)
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
			//->whereIn($a . '.access', $access)
			->whereIn($p . '.access', $access)
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
			->whereIn($p . '.access', $access)
			//->whereIn($a . '.access', $access)
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
