<?php

namespace App\Modules\Users\Events;

use App\Modules\Users\Models\Note;

class NoteCreated
{
	/**
	 * @var array
	 */
	public $data;

	/**
	 * @var Note
	 */
	public $note;

	/**
	 * Constructor
	 *
	 * @param Note $note
	 * @param array $data
	 * @return void
	 */
	public function __construct(Note $note, array $data)
	{
		$this->data = $data;
		$this->note = $note;
	}

	/**
	 * Return the entity
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function getNote()
	{
		return $this->note;
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
