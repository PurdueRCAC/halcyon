<?php
namespace App\Modules\Users\Events;

use App\Modules\Users\Models\User;

class UserDisplay
{
	/**
	 * The user
	 *
	 * @var User
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
	public function __construct(User $user, $active)
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
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Set the body
	 *
	 * @param  User  $user
	 * @return void
	 */
	public function setUser(User $user)
	{
		$this->user = $user;
	}
}
