<?php

namespace App\Modules\Software\Events;

use App\Modules\Software\Models\Application;

class ApplicationUpdated
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
