<?php

namespace App\Modules\Themes\Contracts;

interface PublisherInterface
{
	/**
	 * Publish something.
	 *
	 * @return mixed
	 */
	public function publish();
}
