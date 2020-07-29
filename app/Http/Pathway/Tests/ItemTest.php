<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Http\Pathway\Tests;

use App\Http\Test\Basic;
use App\Http\Pathway\Item;

/**
 * Pathway trail item tests
 */
class ItemTest extends Basic
{
	/**
	 * Tests that data passed in constructor is set to correct properties
	 *
	 * @return  void
	 **/
	public function testConstructor()
	{
		$name = 'Crumb';
		$link = 'index.php?option=com_example';

		$item = new Item($name, $link);

		$this->assertEquals($item->name, $name);
		$this->assertEquals($item->link, $link);
	}
}
