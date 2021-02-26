<?php

namespace App\Modules\Knowledge\Events;

use App\Modules\Knowledge\Models\Feedback;

class FeedbackCreated
{
	/**
	 * @var Feedback
	 */
	public $feedback;

	/**
	 * Constructor
	 *
	 * @param Feedback $feedback
	 * @return void
	 */
	public function __construct(Feedback $feedback)
	{
		$this->feedback = $feedback;
	}
}
