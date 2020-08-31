<?php

namespace App\Modules\Pages\Events;

class PageMetadata
{
	/**
	 * The page
	 *
	 * @var object
	 */
	public $page;

	/**
	 * Constructor
	 *
	 * @param  Page $page
	 * @return void
	 */
	public function __construct($page)
	{
		$this->page = $page;
	}
}
