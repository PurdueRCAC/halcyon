<?php

namespace App\Modules\Tags\Events;

class TagCreating
{
	/**
	 * @var Tag
	 */
	public $tag;

	/**
	 * Constructor
	 *
	 * @param  Tag $tag
	 * @return void
	 */
	public function __construct(Tag $tag)
	{
		$this->tag = $tag;
	}
}
