<?php
namespace App\Http\Pathway\Tests;

use PHPUnit_Framework_TestCase;
use App\Http\Pathway\Item;

/**
 * Pathway trail item tests
 */
class ItemTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Tests that data passed in constructor is set to correct properties
	 *
	 * @return  void
	 **/
	public function testConstructor()
	{
		$name = 'Crumb';
		$link = '/example';

		$item = new Item($name, $link);

		$this->assertEquals($item->name, $name);
		$this->assertEquals($item->link, $link);
	}
}
