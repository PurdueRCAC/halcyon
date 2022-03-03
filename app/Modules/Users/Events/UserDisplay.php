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
	 * Content parts
	 *
	 * @var string
	 */
	private $parts;

	/**
	 * Constructor
	 *
	 * @param  object $user
	 * @param  string $active
	 * @return void
	 */
	public function __construct(User $user, $active)
	{
		$this->user = $user;
		$this->active = $active;
		$this->sections = array();
	}

	/**
	 * Get active section
	 *
	 * @return string
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function getSections()
	{
		return $this->sections;
	}

	/**
	 * Get parts
	 *
	 * @return array
	 */
	public function getParts()
	{
		return $this->parts;
	}

	/**
	 * Add a content section
	 *
	 * @param  string  $route
	 * @param  string  $name
	 * @param  bool    $active
	 * @param  string  $content
	 * @return void
	 */
	public function addSection($route, $name, $active = false, $content = null)
	{
		$this->sections[$name] = array(
			'route'   => $route,
			'name'    => $name,
			'active'  => $active,
			'content' => $content,
			'parts'   => array(),
		);
	}

	/**
	 * Add a part
	 *
	 * @param  string  $content
	 * @return void
	 */
	public function addPart($content)
	{
		$this->parts[] = $content;
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
