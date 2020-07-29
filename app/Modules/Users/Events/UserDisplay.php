<?php

namespace App\Modules\Users\Events;

class UserDisplay
{
	/**
	 * The user
	 *
	 * @var string
	 */
	private $user;

	/**
	 * Active section
	 *
	 * @var string
	 */
	private $active;

	/**
	 * Content sections
	 *
	 * @var string
	 */
	private $sections;

	/**
	 * Constructor
	 *
	 * @param  object $user
	 * @return void
	 */
	public function __construct($user, $active)
	{
		$this->user = $user;
		$this->active = $active;
		$this->sections = array();
	}

	/**
	 * Get the user
	 *
	 * @return string
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * Get the user
	 *
	 * @return string
	 */
	public function getSections()
	{
		return $this->sections;
	}

	/**
	 * Get the user
	 *
	 * @return void
	 */
	public function addSection($route, $name, $active = false, $content = null)
	{
		$this->sections[] = array(
			'route'   => $route,
			'name'    => $name,
			'active'  => $active,
			'content' => $content,
		);
	}

	/**
	 * Get the user
	 *
	 * @return string
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Set the body
	 *
	 * @param string $body
	 * @return void
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}
}
