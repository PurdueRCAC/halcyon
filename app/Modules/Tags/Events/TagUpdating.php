<?php

namespace App\Modules\Tags\Events;

use App\Modules\Tags\Models\Tag;

class TagUpdating
{
	/**
	 * @var Tag
	 */
	private $tag;

	public function __construct(Tag $tag)
	{
		$this->tag = $tag;
	}

	/**
	 * @return User
	 */
	public function getTag()
	{
		return $this->tag;
	}
}
