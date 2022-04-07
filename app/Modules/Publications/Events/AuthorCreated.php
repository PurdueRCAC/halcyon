<?php

namespace App\Modules\Publications\Events;

use App\Modules\Publications\Models\Author;

class AuthorCreated
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
