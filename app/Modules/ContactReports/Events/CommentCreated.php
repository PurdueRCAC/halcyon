<?php

namespace App\Modules\ContactReports\Events;

use App\Modules\ContactReports\Models\Comment;

class CommentCreated
{
	/**
	 * @var Comment
	 */
	public $comment;

	/**
	 * Constructor
	 *
	 * @param  Comment $comment
	 * @return void
	 */
	public function __construct(Comment $comment)
	{
		$this->comment = $comment;
	}
}
