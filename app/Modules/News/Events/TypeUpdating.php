<?php

namespace App\Modules\News\Events;

use App\Modules\News\Models\Type;

class TypeUpdating
{
	/**
	 * @var Type
	 */
	private $type;

	/**
	 * Constructor
	 *
	 * @param Type $type
	 * @return void
	 */
	public function __construct(Type $type)
	{
		$this->type = $type;
	}

	/**
	 * @return Article
	 */
	public function getType()
	{
		return $this->type;
	}
}
