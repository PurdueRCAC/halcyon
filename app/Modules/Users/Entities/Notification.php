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
	 * @var string
	 */
	public $content;

	/**
	 * The notificaiton level
	 *
	 * @var string
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
	public function __construct(string $title, string $content, string $level = 'normal')
	{
		$this->title = $title;
		$this->content = $content;
		$this->level = $level;
	}

	/**
	 * Return data as an array
	 *
	 * @return array<string,string>
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
	 * Return data as a string
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		$str = json_encode($this->toArray());
		$str = $str ?: '{"title":"","content":"","level":"normal"}';

		return $str;
	}
}
