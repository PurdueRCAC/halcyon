<?php

namespace App\Modules\Issues\Events;

use App\Modules\Issues\Models\Issue;

class IssueCreated
{
	/**
	 * @var Issue
	 */
	public $issue;

	/**
	 * Constructor
	 *
	 * @param Issue $issue
	 * @return void
	 */
	public function __construct(Issue $issue)
	{
		$this->issue = $issue;
	}
}
