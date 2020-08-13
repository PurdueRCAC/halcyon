<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Config\Tests;

use App\Halcyon\Test\Basic;
use App\Halcyon\Config\Legacy;

/**
 * Legacy tests
 */
class LegacyTest extends Basic
{
	/**
	 * Tests reading an invalid file path
	 *
	 * @covers  \App\Halcyon\Config\Legacy::read
	 * @return  void
	 **/
	public function testReadErrorsWithInvalidFilePath()
	{
		$path = __DIR__ . '/Files';

		$loader = new Legacy($path);

		$this->setExpectedException('App\Halcyon\\Config\\Exception\\FileNotFoundException');

		$loader->read($path . '/configuration.php');
	}

	/**
	 * Tests reading an invalid file
	 *
	 * @covers  \App\Halcyon\Config\Legacy::read
	 * @return  void
	 **/
	public function testReadErrorsWithInvalidFile()
	{
		$path = __DIR__ . '/Files';

		$loader = new Legacy($path);

		$this->setExpectedException('App\Halcyon\\Config\\Exception\\UnsupportedFormatException');

		$loader->read($path . '/Legacy/Invalid/configuration.php');
	}

	/**
	 * Tests constructor
	 *
	 * @covers  \App\Halcyon\Config\Legacy::__construct
	 * @covers  \App\Halcyon\Config\Legacy::exists
	 * @return  void
	 **/
	public function testExists()
	{
		$path = __DIR__ . '/Files/Legacy';

		$loader = new Legacy($path);

		$this->assertTrue($loader->exists());

		$path = __DIR__ . '/Files/Repository';

		$loader = new Legacy($path);

		$this->assertFalse($loader->exists());
	}

	/**
	 * Tests reading an invalid file
	 *
	 * @covers  \App\Halcyon\Config\Legacy::read
	 * @return  void
	 **/
	public function testRead()
	{
		if (!defined('PATH_ROOT'))
		{
			define('PATH_ROOT', '/var/www/hub');
		}

		if (!defined('PATH_APP'))
		{
			define('PATH_APP', PATH_ROOT . '/app');
		}

		$path = __DIR__ . '/Files';

		$loader = new Legacy($path);

		$file = $loader->read($path . '/Legacy/configuration.php');

		$this->assertInstanceOf('JConfig', $file);
		$this->assertEquals(PATH_APP . '/tmp', $file->tmp_path);
		$this->assertEquals(PATH_APP . '/logs', $file->log_path);
	}
}
