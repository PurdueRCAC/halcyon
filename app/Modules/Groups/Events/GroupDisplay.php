<?php

namespace App\Modules\Groups\Events;

use App\Modules\Groups\Models\Group;

class GroupDisplay
{
	/**
	 * The group
	 *
	 * @var string
	 */
	public $group;

	/**
	 * Active section
	 *
	 * @var string
	 */
	public $active;

	/**
	 * Content sections
	 *
	 * @var string
	 */
	public $sections;

	/**
	 * Constructor
	 *
	 * @param  Group $group
	 * @return void
	 */
	public function __construct(Group $group, $active)
	{
		$this->group = $group;
		$this->active = $active;
		$this->sections = array();
	}

	/**
	 * Get active section name
	 *
	 * @return string
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * Get all sections
	 *
	 * @return string
	 */
	public function getSections()
	{
		return $this->sections;
	}

	/**
	 * Add a section
	 *
	 * @return void
	 */
	public function addSection(string $route, string $name, bool $active = false, $content = null)
	{
		$this->sections[] = array(
			'route'   => $route,
			'name'    => $name,
			'active'  => $active,
			'content' => $content,
		);
	}

	/**
	 * Get the group
	 *
	 * @return string
	 */
	public function getGroup()
	{
		return $this->group;
	}
}
