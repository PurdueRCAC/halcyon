<?php

namespace App\Modules\ContactReports\Events;

use App\Modules\ContactReports\Models\Comment;

class CommentUpdating
{
	/**
	 * @var Comment
	 */
	private $comment;

	public function __construct(Comment $comment)
	{
		$this->comment = $comment;
	}

	/**
	 * @return Report
	 */
	public function getComment()
	{
		return $this->comment;
	}
}
