<?php

namespace App\Modules\Pages\Events;

use App\Modules\Pages\Models\Page;

class PageUpdated
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
