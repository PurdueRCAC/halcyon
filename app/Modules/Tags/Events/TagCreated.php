<?php

namespace App\Modules\Tags\Events;

use App\Modules\Tags\Models\Tag;

class TagCreated
{
	/**
	 * @var array
	 */
	public $data;

	/**
	 * @var Tag
	 */
	public $tag;

	/**
	 * Constructor
	 *
	 * @param Tag $tag
	 * @param array $data
	 * @return void
	 */
	public function __construct(Tag $tag, array $data)
	{
		$this->data = $data;
		$this->tag = $tag;
	}

	/**
	 * Return the entity
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function getTag()
	{
		return $this->tag;
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
