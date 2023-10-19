<?php

namespace App\Modules\Software\Events;

use App\Modules\Software\Models\Version;

class VersionDeleted
{
	/**
	 * @var Version
	 */
	public $version;

	/**
	 * Constructor
	 *
	 * @param Version $version
	 * @return void
	 */
	public function __construct(Version $version)
	{
		$this->version = $version;
	}
}
