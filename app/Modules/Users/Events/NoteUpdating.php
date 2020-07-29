<?php

namespace App\Modules\Users\Events;

use App\Modules\Users\Models\Note;

class NoteUpdating
{
	/**
	 * @var Note
	 */
	private $note;

	public function __construct(Note $note)
	{
		$this->note = $note;
	}

	/**
	 * @return User
	 */
	public function getNote()
	{
		return $this->note;
	}
}
