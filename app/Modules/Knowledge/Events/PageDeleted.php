<?php

namespace App\Modules\Knowledge\Events;

use App\Modules\Knowledge\Models\Page;

class PageDeleted
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
	public function __construct($page)
	{
		$this->page = $page;
	}
}
