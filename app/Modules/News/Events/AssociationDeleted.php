<?php

namespace App\Modules\News\Events;

use App\Modules\News\Models\Association;

class AssociationDeleted
{
	/**
	 * @var Association
	 */
	public $association;

	/**
	 * Constructor
	 *
	 * @param  Association $association
	 * @return void
	 */
	public function __construct(Association $association)
	{
		$this->association = $association;
	}
}
