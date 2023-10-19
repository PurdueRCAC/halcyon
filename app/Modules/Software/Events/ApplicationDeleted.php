<?php

namespace App\Modules\Software\Events;

use App\Modules\Software\Models\Application;

class ApplicationDeleted
{
	/**
	 * @var Application
	 */
	public $publication;

	/**
	 * Constructor
	 *
	 * @param Application $publication
	 * @return void
	 */
	public function __construct(Application $publication)
	{
		$this->publication = $publication;
	}
}
