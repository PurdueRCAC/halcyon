<?php

namespace App\Modules\Tags\Events;

use App\Modules\Tags\Models\Tag;

class TagCreated
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
