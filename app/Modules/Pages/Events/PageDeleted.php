<?php

namespace App\Modules\Pages\Events;

use App\Modules\Pages\Models\Page;

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
