<?php

namespace App\Modules\Publications\Events;

use App\Modules\Publications\Models\Author;

class AuthorDeleted
{
	/**
	 * @var Author
	 */
	public $author;

	/**
	 * Constructor
	 *
	 * @param Author $author
	 * @return void
	 */
	public function __construct(Author $author)
	{
		$this->author = $author;
	}
}
