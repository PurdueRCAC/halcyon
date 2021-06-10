<?php
namespace App\Listeners\Resources\Knowledge;

use App\Modules\Resources\Events\AssetDisplaying;
use App\Modules\Resources\Events\AssetDeleted;
use App\Modules\Knowledge\Models\Page;
use App\Modules\Knowledge\Models\Associations;
use App\Modules\Knowledge\Models\Association;

/**
 * Knowledge base listener for Resources
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
		$events->listen(AssetDeleted::class, self::class . '@handleAssetDeleted');
	}

	/**
	 * Unpublish linked pages when a resource is trashed
	 *
	 * @param   AssetDeleted  $event
	 * @return  void
	 */
	public function handleAssetDeleted(AssetDeleted $event)
	{
		$a = (new Associations)->getTable();
		$p = (new Page)->getTable();

		$assoc = Associations::query()
			->select($a . '.*')
			->join($p, $p . '.id', $a . '.page_id')
			->where($a . '.path', '=', $event->asset->listname)
			->where($a . '.state', '=', 1)
			->orderBy($a . '.id', 'asc')
			->get()
			->first();

		if (!$assoc)
		{
			return;
		}

		$assoc->state = 1;
		$assoc->save();
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   AssetDisplaying  $event
	 * @return  void
	 */
	public function handleAssetDisplaying(AssetDisplaying $event)
	{
		if (app()->has('isAdmin') && app()->get('isAdmin'))
		{
			return;
		}

		//$content = $event->getBody();

		$access = [1];

		if (auth()->user())
		{
			$access = auth()->user()->getAuthorisedViewLevels();
		}

		$a = (new Associations)->getTable();
		$p = (new Page)->getTable();

		$assoc = Associations::query()
			->select($a . '.*')
			->join($p, $p . '.id', $a . '.page_id')
			->where($a . '.path', '=', $event->getAsset()->listname)
			->where($a . '.state', '=', 1)
			->whereIn($a . '.access', $access)
			->orderBy($a . '.id', 'asc')
			->get()
			->first();
		if (!$assoc)
		{
			return;
		}

		$page = $assoc->page;

		if (!$page)
		{
			return;
		}

		$overview = $page->children()
			->where('alias', '=', 'overview')
			->where($a . '.state', '=', 1)
			->whereIn($a . '.access', $access)
			->get()
			->first();

		if ($overview && $event->getActive() != 'guide')
		{
			$overview->variables->merge($page->variables);

			$event->addSection(
				route('site.knowledge.page', ['uri' => $page->alias]),
				$page->headline,
				true,
				$overview->body
			);
		}
		else
		{
			if (!$page->content || $page->params->get('show_toc', 1))
			{
				$page->content .= '<h1>' . $page->headline . '</h1>';

				$childs = $assoc->publishedChildren();

				if (count($childs))
				{
					$page->content .= '<ul class="kb-toc">';
					foreach ($childs as $n)
					{
						$n->page->variables->merge($page->variables);
						//$pa = $p ? $p . '/' . $n->page->alias : $n->page->alias;

						$page->content .= '<li>';
						$page->content .= '<a href="' . route('site.knowledge.page', ['uri' => $n->path]) . '">' . $n->page->headline . '</a>';
						$page->content .= '</li>';
					}
					$page->content .= '</ul>';
				}
			}

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
			->where($a . '.state', '=', 1)
			->whereIn($a . '.access', $access)
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
			->where($a . '.state', '=', 1)
			->whereIn($a . '.access', $access)
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
