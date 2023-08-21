<?php
namespace App\Modules\Users\Events;

use Illuminate\Http\Request;

class Authenticators
{
	/**
	 * @var array<string,array{string,mixed}>
	 */
	public $authenticators = array();

	/**
	 * Add an authenticators
	 *
	 * @param  string $name
	 * @param  array<string,array{string,mixed}> $options
	 * @return void
	 */
	public function addAuthenticator($name, $options = array()): void
	{
		$this->authenticators[$name] = $options;
	}
}
