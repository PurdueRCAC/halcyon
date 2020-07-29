<?php

namespace App\Modules\Users\Events;

use App\Modules\Users\Models\Note;

class NoteDeleted
{
	/**
	 * @var Note
	 */
	public $note;

	/**
	 * Constructor
	 *
	 * @param  Note $note
	 * @return void
	 */
	public function __construct(Note $note)
	{
		$this->note = $note;
	}
}
