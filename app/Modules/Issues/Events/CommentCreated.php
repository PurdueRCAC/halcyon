<?php

namespace App\Modules\Issues\Events;

use App\Modules\Issues\Models\Comment;

class CommentCreated
{
	/**
	 * @var array
	 */
	public $data;

	/**
	 * @var Comment
	 */
	public $comment;

	/**
	 * Constructor
	 *
	 * @param Comment $comment
	 * @param array $data
	 * @return void
	 */
	public function __construct(Comment $comment, array $data)
	{
		$this->data = $data;
		$this->comment = $comment;
	}

	/**
	 * Return the entity
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * Return ALL data sent
	 *
	 * @return array
	 */
	public function getSubmissionData()
	{
		return $this->data;
	}
}
