<?php

namespace App\Modules\Users\Entities;

class Notification
{
	/**
	 * Title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * The notification body
	 *
	 * @var  string
	 */
	public $content;

	/**
	 * The notificaiton level
	 *
	 * @var  string
	 */
	public $level;

	/**
	 * Constructor
	 *
	 * @param   string  $title
	 * @param   string  $content
	 * @param   string  $level
	 * @return  void
	 */
	public function __construct($title, $content, $level = 'normal')
	{
		$this->title = $title;
		$this->content = $content;
		$this->level = $level;
	}

	/**
	 * Get json contents from the cache, setting as needed.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return array(
			'title'   => $this->title,
			'content' => $this->content,
			'level'   => $this->level
		);
	}

	/**
	 * Handle call __toString.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return json_encode($this->toArray());
	}
}
