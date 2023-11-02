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
	 * @var array<string,array<string,mixed>>
	 */
	private $sections;

	/**
	 * Content parts
	 *
	 * @var array<int,string>
	 */
	private $parts;

	/**
	 * Constructor
	 *
	 * @param  User $user
	 * @param  string $active
	 * @return void
	 */
	public function __construct(User $user, $active)
	{
		$this->user = $user;
		$this->active = $active;
		$this->sections = array();
		$this->parts = array();
	}

	/**
	 * Get active section
	 *
	 * @return string|null
	 */
	public function getActive(): ?string
	{
		return $this->active;
	}

	/**
	 * Get sections
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function getSections(): array
	{
		return $this->sections;
	}

	/**
	 * Get parts
	 *
	 * @return array<int,string>
	 */
	public function getParts(): array
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
	public function addSection($route, $name, $active = false, $content = null): void
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
	 * Append a content section
	 *
	 * @param  string  $name
	 * @param  string  $content
	 * @return void
	 */
	public function appendSection($name, $content): void
	{
		$this->sections[$name]['content'] .= $content;
	}

	/**
	 * Add a part
	 *
	 * @param  string  $content
	 * @return void
	 */
	public function addPart($content): void
	{
		$this->parts[] = $content;
	}

	/**
	 * Get the user
	 *
	 * @return User
	 */
	public function getUser(): User
	{
		return $this->user;
	}

	/**
	 * Set the body
	 *
	 * @param  User  $user
	 * @return void
	 */
	public function setUser(User $user): void
	{
		$this->user = $user;
	}
}
