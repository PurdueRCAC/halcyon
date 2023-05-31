<?php
namespace App\Modules\Users\Events;

use Illuminate\Http\Request;

class Authenticators
{
	/**
	 * @var array<string,array>
	 */
	public $authenticators = array();

	/**
	 * Add an authenticators
	 *
	 * @param  string $name
	 * @param  array $options
	 * @return void
	 */
	public function addAuthenticator($name, $options = array()): void
	{
		$this->authenticators[$name] = $options;
	}
}
