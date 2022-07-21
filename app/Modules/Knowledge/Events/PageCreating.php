<?php

namespace App\Modules\Knowledge\Events;

use App\Modules\Knowledge\Models\Page;

class PageCreating
{
	/**
	 * @var Page
	 */
	public $page;

	/**
	 * Constructor
	 *
	 * @param Page $page
	 * @return void
	 */
	public function __construct(Page $page)
	{
		$this->page = $page;
	}
}
