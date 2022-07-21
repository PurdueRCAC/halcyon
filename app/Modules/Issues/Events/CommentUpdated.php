<?php

namespace App\Modules\Issues\Events;

use App\Modules\Issues\Models\Comment;

class CommentUpdated
{
	/**
	 * @var Comment
	 */
	public $comment;

	/**
	 * Constructor
	 *
	 * @param Comment $comment
	 * @return void
	 */
	public function __construct(Comment $comment)
	{
		$this->comment = $comment;
	}
}
