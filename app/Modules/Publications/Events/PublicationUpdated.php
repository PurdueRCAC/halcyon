<?php

namespace App\Modules\Publications\Events;

use App\Modules\Publications\Models\Publication;

class PublicationUpdated
{
	/**
	 * @var Publication
	 */
	public $publication;

	/**
	 * Constructor
	 *
	 * @param Publication $publication
	 * @return void
	 */
	public function __construct(Publication $publication)
	{
		$this->publication = $publication;
	}
}
